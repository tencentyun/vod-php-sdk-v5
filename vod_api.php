<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once './include.php';

class Vodapi {
    private static function getErrRsp($id, $code, $msg, $data = NULL) {
        $rsp = array (
            'code' => $code,
            'message' => $msg,
            'vodRequestId' => $id,
        );
        if (isset($data)) {
            $rsp['data'] = $data;
        }
        return $rsp;
    }

    private static function applyUpload($id, array $package) {
        $config = array('SecretId'       => Conf::SECRET_ID,
                        'SecretKey'      => Conf::SECRET_KEY,
                        'RequestMethod'  => 'POST');

        $vod = QcloudApi::load(QcloudApi::MODULE_VOD, $config);
        $msg = "ApplyUpload req package:" . json_encode($package);
        vodLog($msg, $id);

        for ($retry = 0; $retry < 3; $retry ++) {
            if ($retry > 0) {
                $msg = "ApplyUpload retry at " . $retry;
                vodLog($msg, $id);
            }
            $rsp = $vod->ApplyUpload($package);
            if ($rsp == false) {
                $error = $vod->getError();
                $msg = "ApplyUpload failed, code: " . $error->getCode() . ", message: " . $error->getMessage() . "ext: " . var_export($error->getExt(), true);
                vodLog($msg, $id);
            } else {
                break;
            }
        }
        if ($rsp == false) {
            return false;
        }

        $msg = "ApplyUpload|recv:" . json_encode($rsp);
        vodLog($msg, $id);
        return $rsp;
    }

    private static function commitUpload($id, array $package) {
        $config = array('SecretId'       => Conf::SECRET_ID,
                        'SecretKey'      => Conf::SECRET_KEY,
                        'RequestMethod'  => 'POST');

        $vod = QcloudApi::load(QcloudApi::MODULE_VOD, $config);
        $msg = "CommitUpload req package:" . json_encode($package);
        vodLog($msg, $id);

        for ($retry = 0; $retry < 3; $retry ++) {
            if ($retry > 0) {
                $msg = "CommitUpload retry at " . $retry;
                vodLog($msg, $id);
            }
            $rsp = $vod->CommitUpload($package);
            if ($rsp == false) {
                $error = $vod->getError();
                $msg = "CommitUpload failed, code: " . $error->getCode() . ", message: " . $error->getMessage() . "ext: " . var_export($error->getExt(), true);
                vodLog($msg, $id);
            } else {
                break;
            }
        }
        if ($rsp == false) {
            return false;
        }

        $msg = "CommitUpload|recv:" . json_encode($rsp);
        vodLog($msg, $id);
        return $rsp;
    }

    private static function uploadCos($id, array $package) {
        $cosClient = new Qcloud\Cos\Client(
                         array(
                             'region' => 'cos.' . $package['region'],
                             'credentials' => array(
                                 'appId' => $package['appid'],
                                 'secretId' => Conf::SECRET_ID,
                                 'secretKey' => Conf::SECRET_KEY)));
        try {
            $msg = "UploadCos req package:" . json_encode($package);
            vodLog($msg, $id);
            $rsp = $cosClient->upload($package['bucket'], $package['dst'], file_get_contents($package['src']));
            $msg = "UploadCos|requestId:" . $rsp["RequestId"];
            vodLog($msg, $id);
        } catch (\Exception $e) {
            $msg = "UploadCos failed " . $e;
            vodLog($msg, $id);
            return false;
        }
        return true;
    }

    private static function init($id, array $src, &$parameter) {
        date_default_timezone_set('PRC');
        ini_set('memory_limit', '-1');
        if (isset($parameter)) {
            if (!is_array($parameter)) {
                return false;
            }
        } else {
            $parameter = array();
        }

        if (!array_key_exists('sourceContext', $parameter)) {
            $parameter['sourceContext'] = $id;
        }

        $videoPath = $src['videoPath'];
        if (file_exists($videoPath)) {
            $fullFileName = end(explode("/", $videoPath));
            $fileType = strtolower(end(explode(".", $fullFileName)));
            $fileName = reset(explode(".", $fullFileName));

            if (!array_key_exists('videoType', $parameter)) {
                $parameter['videoType'] = $fileType;
            }

            if (!array_key_exists('videoName', $parameter)) {
                $parameter['videoName'] = $fileName;
            }

            $parameter['videoSize'] = filesize($videoPath);
        } else {
            return false;
        }

        if (array_key_exists('coverPath', $src)) {
            $coverPath = $src['coverPath'];
            if (file_exists($coverPath)) {
                $fullFileName = end(explode("/", $coverPath));
                $fileType = strtolower(end(explode(".", $fullFileName)));
                $fileName = reset(explode(".", $fullFileName));

                if (!array_key_exists('coverType', $parameter)) {
                    $parameter['coverType'] = $fileType;
                }

                if (!array_key_exists('coverName', $parameter)) {
                    $parameter['coverName'] = $fileName;
                }

                $parameter['coverSize'] = filesize($coverPath);
            } else {
                return false;
            }
        }
        return true;
    }

    // upload files, assign local path of video and cover in $src, assign optional parameters in $parameter
    public static function upload(array $src, $parameter = null) {
        $id = rand();
        $result = self::init($id, $src, $parameter);
        if ($result == false) {
            return self::getErrRsp($id, 100, "src file or paramter error");
        }

        // apply upload
        $applyUploadResult = self::applyUpload($id, $parameter);
        if ($applyUploadResult == false) {
            return self::getErrRsp($id, 1000, "apply upload failed");
        }

        // upload to cos
        $result = self::uploadCos(
                $id,
                array(
                    'appid' => $applyUploadResult['storageAppId'],
                    'region' => $applyUploadResult['storageRegionV5'],
                    'bucket' => $applyUploadResult['storageBucket'],
                    'src' => $src['videoPath'],
                    'dst' => $applyUploadResult['video']['storagePath'],
                ));
        if ($result == false) {
            return self::getErrRsp($id, 2000, "upload to storage failed");
        }

        if (array_key_exists('coverPath', $src)) {
            $result = self::uploadCos(
                    $id,
                    array(
                         $id,
                         'appid' => $applyUploadResult['storageAppId'],
                         'region' => $applyUploadResult['storageRegionV5'],
                         'bucket' => $applyUploadResult['storageBucket'],
                         'src' => $src['coverPath'],
                         'dst' => $applyUploadResult['cover']['storagePath'],
                    ));
            if ($result == false) {
                return self::getErrRsp($id, 2000, "upload to storage failed");
            }
        }

        // commit upload
        $result = self::commitUpload(
                $id,
                array(
                    'vodSessionKey' => $applyUploadResult['vodSessionKey'],
                ));
        if ($result == false) {
            return self::getErrRsp($id, 3000, "commit upload failed");
        }

        // success
        return self::getErrRsp($id, 0, "success", $result);
    }
}

function vodLog($message, $id = 0) {
    $t = microtime(true);
    $micro = sprintf("%06d",($t - floor($t)) * 1000000);
    $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
    error_log('[' . $d->format("Y-m-d H:i:s.u") . '][' . $id . ']' . $message . "\n", 3, Conf::LOG_PATH);
}
