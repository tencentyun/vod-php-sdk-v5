<?php
/**
 * Created by PhpStorm.
 * User: jianguoxu
 * Date: 2019/1/1
 * Time: 19:23
 */

use PHPUnit\Framework\TestCase;
use Vod\VodUploadClient;
use Vod\Model\VodUploadRequest;
use Vod\Model\VodUploadHttpProfile;
use Vod\Exception\VodClientException;
use TencentCloud\Common\Exception\TencentCloudSDKException;

class VodUploadClientTest extends TestCase
{
    private function getVodUploadClient() {
        $client = new VodUploadClient("Your SecretId", "Your SecretKey");
        // set proxy if necessary
        //$uploadHttpProfile = new VodUploadHttpProfile("http://proxy_ip:proxy_port");
        //$client->setHttpProfile($uploadHttpProfile);
        // set credential token if necessary
        // $client = new VodUploadClient("your secretId", "your secretKey", "your token");
        return $client;
    }

    public function testLackMediaPath() {
        $this->expectException(VodClientException::class);
        $this->expectExceptionMessage("lack media path");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $client->upload("ap-guangzhou", $req);
    }

    public function testLackMediaType() {
        $this->expectException(VodClientException::class);
        $this->expectExceptionMessage("lack media type");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife";
        $client->upload("ap-guangzhou", $req);
    }

    public function testInvalidMediaPath() {
        $this->expectException(VodClientException::class);
        $this->expectExceptionMessage("media path is invalid");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/WildlifeA";
        $client->upload("ap-guangzhou", $req);
    }

    public function testInvalidCoverPath() {
        $this->expectException(VodClientException::class);
        $this->expectExceptionMessage("cover path is invalid");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife.mp4";
        $req->CoverFilePath = "test/Wildlife-CoverA";
        $client->upload("ap-guangzhou", $req);
    }

    public function testLackCoverType() {
        $this->expectException(VodClientException::class);
        $this->expectExceptionMessage("lack cover type");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife.mp4";
        $req->CoverFilePath = "test/Wildlife-Cover";
        $client->upload("ap-guangzhou", $req);
    }

    public function testInvalidMediaType() {
        $this->expectException(TencentCloudSDKException::class);
        $this->expectExceptionMessage("invalid media type");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife.mp4";
        $req->MediaType = "test";
        $client->upload("ap-guangzhou", $req);
    }

    public function testInvalidCoverType() {
        $this->expectException(TencentCloudSDKException::class);
        $this->expectExceptionMessage("invalid cover type");
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife.mp4";
        $req->CoverFilePath = "test/Wildlife-Cover";
        $req->CoverType = "test";
        $client->upload("ap-guangzhou", $req);
    }

    public function testUpload() {
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/Wildlife.mp4";
        $req->CoverFilePath = "test/Wildlife-Cover.png";
        $rsp = $client->upload("ap-guangzhou", $req);
        print_r($rsp);
    }

    public function testHlsUpload() {
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/hls/prog_index.m3u8";
        $rsp = $client->upload("ap-guangzhou", $req);
        print_r($rsp);
    }

    public function testMasterPlaylistUpload() {
        $client = $this->getVodUploadClient();
        $req = new VodUploadRequest();
        $req->MediaFilePath = "test/hls/bipbopall.m3u8";
        $rsp = $client->upload("ap-guangzhou", $req);
        print_r($rsp);
    }
}