<?php
/**
 * Created by PhpStorm.
 * User: jianguoxu
 * Date: 2018/11/4
 * Time: 16:39
 */

namespace Vod;

use Qcloud\Cos\Client;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\ApplyUploadRequest;
use TencentCloud\Vod\V20180717\Models\CommitUploadRequest;
use TencentCloud\Vod\V20180717\Models\ParseStreamingManifestRequest;
use TencentCloud\Vod\V20180717\Models\ParseStreamingManifestResponse;
use TencentCloud\Vod\V20180717\VodClient;
use Vod\Common\FileUtil;
use Vod\Exception\VodClientException as VodClientException;
use Vod\Model\VodUploadRequest;
use Vod\Model\VodUploadResponse;
use \DateTime;

error_reporting(E_ALL ^ E_NOTICE);

/**
 * VOD upload client
 *
 * Class VodUploadClient
 * @package Vod
 */
class VodUploadClient
{
    private $secretId;

    private $secretKey;

    private $token;

    private $ignoreCheck;

    private $retryTime;

    private $logPath;

    private $httpProfile;

    public function __construct($secretId, $secretKey, $token = null, $logPath = './vod_upload.log')
    {
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->token = $token;
        $this->ignoreCheck = false;
        $this->retryTime = 3;
        $this->logPath = $logPath;
        date_default_timezone_set('PRC');
    }

    /**
     * Upload
     *
     * @param $region
     * @param $uploadRequest
     * @return VodUploadResponse
     */
    public function upload($region, $uploadRequest)
    {
        if (!$this->ignoreCheck) {
            $this->prefixCheckAndSetDefaultVal($region, $uploadRequest);
        }

        $cosConfig = array();
        $cloudProfile = null;
        if (!empty($this->httpProfile) && !empty($this->httpProfile->Proxy)) {
            $cloudProfile = new ClientProfile();
            $cloudHttpProfile = new HttpProfile();
            $cloudHttpProfile->setProxy($this->httpProfile->Proxy);
            $cloudProfile->setHttpProfile($cloudHttpProfile);
            $cosConfig['proxy'] = $this->httpProfile->Proxy;
            $this->log("INFO", "Proxy set ok");
        }

        $credential = new Credential($this->secretId, $this->secretKey, $this->token);
        $vodClient = new VodClient($credential, $region, $cloudProfile);

        $parsedManifestList = array();
        $segmentFilePathList = array();
        if ($this->isManifestMediaType($uploadRequest->MediaType)) {
            $this->parseManifest($vodClient, $uploadRequest->MediaFilePath, $uploadRequest->MediaType, $parsedManifestList, $segmentFilePathList);
        }

        $this->log("INFO", "Upload Request = ".$uploadRequest->toJsonString());
        $uploadRequestData = $uploadRequest->serialize();
        $applyUploadRequest = new ApplyUploadRequest();
        $applyUploadRequest->deserialize($uploadRequestData);
        $applyUploadResponse = $this->applyUpload($vodClient, $applyUploadRequest);
        $this->log("INFO", "ApplyUpload Response = ".$applyUploadResponse->toJsonString());

        $cosCredential = array();
        if (isset($applyUploadResponse->TempCertificate)) {
            $certificate = $applyUploadResponse->TempCertificate;
            $cosCredential['secretId'] = $certificate->SecretId;
            $cosCredential['secretKey'] = $certificate->SecretKey;
            $cosCredential['token'] = $certificate->Token;
        } else {
            $cosCredential['secretId'] = $this->secretId;
            $cosCredential['secretKey'] = $this->secretKey;
        }
        $cosConfig['region'] = $applyUploadResponse->StorageRegion;
        $cosConfig['credentials'] = $cosCredential;
        $cosClient = new Client($cosConfig);

        if (!empty($uploadRequest->MediaType) && !empty($applyUploadResponse->MediaStoragePath)) {
            $this->uploadCos(
                $cosClient,
                $uploadRequest->MediaFilePath,
                $applyUploadResponse->StorageBucket,
                $applyUploadResponse->MediaStoragePath
            );
        }
        if (!empty($uploadRequest->CoverType) && !empty($applyUploadResponse->CoverStoragePath)) {
            $this->uploadCos(
                $cosClient,
                $uploadRequest->CoverFilePath,
                $applyUploadResponse->StorageBucket,
                $applyUploadResponse->CoverStoragePath
            );
        }

        if (!empty($segmentFilePathList)) {
            foreach ($segmentFilePathList as $segmentFilePath) {
                $storageDir = dirname($applyUploadResponse->MediaStoragePath);
                $mediaFileDir = dirname($uploadRequest->MediaFilePath);
                $segmentRelativeFilePath = substr($segmentFilePath, strlen($mediaFileDir));
                $segmentStoragePath = FileUtil::joinPath($storageDir, $segmentRelativeFilePath);
                $this->uploadCos(
                    $cosClient,
                    $segmentFilePath,
                    $applyUploadResponse->StorageBucket,
                    $segmentStoragePath
                );
            }
        }

        $commitUploadRequest = new CommitUploadRequest();
        $commitUploadRequest->VodSessionKey = $applyUploadResponse->VodSessionKey;
        $commitUploadRequest->SubAppId = $uploadRequest->SubAppId;
        $commitUploadResponse = $this->commitUpload($vodClient, $commitUploadRequest);
        $this->log("INFO", "CommitUpload Response = ".$commitUploadResponse->toJsonString());

        $commitUploadResponseData = $commitUploadResponse->serialize();
        $uploadResponse = new VodUploadResponse();
        $uploadResponse->deserialize($commitUploadResponseData);

        return $uploadResponse;
    }

    private function uploadCos($cosClient, $localPath, $bucket, $cosPath)
    {
        $cosClient->Upload($bucket, $cosPath, fopen($localPath, 'rb'));
    }

    /**
     * Apply for upload
     *
     * @param $vodClient
     * @param $uploadRequest
     * @return mixed
     * @throws TencentCloudSDKException
     */
    private function applyUpload($vodClient, $uploadRequest)
    {
        $err = null;
        for ($i = 0; $i < $this->retryTime; $i++) {
            try {
                $applyUploadResponse = $vodClient->ApplyUpload($uploadRequest);
                return $applyUploadResponse;
            } catch (TencentCloudSDKException $e) {
                if (empty($e->getRequestId())) {
                    $err = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw $err;
    }

    /**
     * Confirm upload
     *
     * @param $vodClient
     * @param $commitUploadRequest
     * @return mixed
     * @throws TencentCloudSDKException
     */
    private function commitUpload($vodClient, $commitUploadRequest)
    {
        $err = null;
        for ($i = 0; $i < $this->retryTime; $i++) {
            try {
                $commitUploadResponse = $vodClient->CommitUpload($commitUploadRequest);
                return $commitUploadResponse;
            } catch (TencentCloudSDKException $e) {
                if (empty($e->getRequestId())) {
                    $err = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw $err;
    }

    /**
     * Parse index file
     *
     * @param $vodClient
     * @param $commitUploadRequest
     * @return mixed
     * @throws TencentCloudSDKException
     */
    private function parseStreamingManifest($vodClient, $parseStreamingManifestRequest)
    {
        $err = null;
        for ($i = 0; $i < $this->retryTime; $i++) {
            try {
                return $parseStreamingManifestResponse = $vodClient->ParseStreamingManifest($parseStreamingManifestRequest);
            } catch (TencentCloudSDKException $e) {
                if (empty($e->getRequestId())) {
                    $err = $e;
                    continue;
                }
                throw $e;
            }
        }
        throw $err;
    }

    /**
     * Pre-check and set default values
     *
     * @param $region
     * @param $uploadRequest
     * @throws VodClientException
     */
    private function prefixCheckAndSetDefaultVal($region, VodUploadRequest $uploadRequest)
    {
        if (empty($region)) {
            throw new VodClientException("lack region");
        }
        if (empty($uploadRequest->MediaFilePath)) {
            throw new VodClientException("lack media path");
        }
        if (!file_exists($uploadRequest->MediaFilePath)) {
            throw new VodClientException("media path is invalid");
        }
        if (empty($uploadRequest->MediaType)) {
            $mediaType = FileUtil::getFileType($uploadRequest->MediaFilePath);
            if (empty($mediaType)) {
                throw new VodClientException("lack media type");
            }
            $uploadRequest->MediaType = $mediaType;
        }
        if (empty($uploadRequest->MediaName)) {
            $uploadRequest->MediaName = FileUtil::getFileName($uploadRequest->MediaFilePath);
        }

        if (!empty($uploadRequest->CoverFilePath)) {
            if (!file_exists($uploadRequest->CoverFilePath)) {
                throw new VodClientException("cover path is invalid");
            }
            if (empty($uploadRequest->CoverType)) {
                $coverType = FileUtil::getFileType($uploadRequest->CoverFilePath);
                if (empty($coverType)) {
                    throw new VodClientException("lack cover type");
                }
                $uploadRequest->CoverType = $coverType;
            }
        }
    }

    /**
     * Parse index file
     *
     * @param $vodClient
     * @param $manifestFilePath
     * @param $manifestMediaType
     * @param $segmentFilePathList
     * @throws TencentCloudSDKException
     */
    private function parseManifest($vodClient, $manifestFilePath, $manifestMediaType, &$parsedManifestList, &$segmentFilePathList)
    {
        if (in_array($manifestFilePath, $parsedManifestList)) {
            return;
        } else {
            array_push($parsedManifestList, $manifestFilePath);
        }

        $manifestContent = file_get_contents($manifestFilePath);
        $parseStreamingManifestRequest = new ParseStreamingManifestRequest();
        $parseStreamingManifestRequest->setMediaManifestContent($manifestContent);
        $parseStreamingManifestRequest->setManifestType($manifestMediaType);
        $parseStreamingManifestResponse = $this->parseStreamingManifest($vodClient, $parseStreamingManifestRequest);

        if (!empty($parseStreamingManifestResponse->MediaSegmentSet)) {
            foreach ($parseStreamingManifestResponse->MediaSegmentSet as $segment) {
                $mediaType = FileUtil::getFileType($segment);
                $mediaFilePath = FileUtil::joinPath(dirname($manifestFilePath), $segment);
                if (!file_exists($mediaFilePath)) {
                    throw new VodClientException("manifest file is invalid");
                }
                array_push($segmentFilePathList, $mediaFilePath);
                if ($this->isManifestMediaType($mediaType)) {
                    $this->parseManifest($vodClient, $mediaFilePath, $mediaType, $parsedManifestList, $segmentFilePathList);
                }
            }
        }
    }

    /**
     *  Determine whether it is an index file
     *
     * @param $mediaType
     * @return bool
     */
    private function isManifestMediaType($mediaType)
    {
        return $mediaType == 'm3u8' || $mediaType == 'mpd';
    }

    private function log($level, $message)
    {
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        error_log('[' . $d->format("Y-m-d H:i:s.u") . '][' . $level . ']' . $message . "\n", 3, $this->logPath);
    }

    /**
     * @return mixed
     */
    public function getSecretId()
    {
        return $this->secretId;
    }

    /**
     * @param mixed $secretId
     */
    public function setSecretId($secretId)
    {
        $this->secretId = $secretId;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getIgnoreCheck()
    {
        return $this->ignoreCheck;
    }

    /**
     * @param mixed $ignoreCheck
     */
    public function setIgnoreCheck($ignoreCheck)
    {
        $this->ignoreCheck = $ignoreCheck;
    }

    /**
     * @return mixed
     */
    public function getRetryTime()
    {
        return $this->retryTime;
    }

    /**
     * @param mixed $retryTime
     */
    public function setRetryTime($retryTime)
    {
        $this->retryTime = $retryTime;
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * @param $logPath
     */
    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * @return mixed
     */
    public function getHttpProfile()
    {
        return $this->httpProfile;
    }

    /**
     * @param mixed $httpProfile
     */
    public function setHttpProfile($httpProfile)
    {
        $this->httpProfile = $httpProfile;
    }
}
