<?php
require './cos-sdk-v5/cos-autoloader.php';
require './qcloudapi-sdk-php/src/QcloudApi/QcloudApi.php';
require '../../src/Vod/VodApi.php';
require '../../src/Vod/Conf.php';

use Vod\VodApi;

VodApi::initConf("your secretId", "your secretKey");

// 上传视频
$result = VodApi::upload(
    array (
        'videoPath' => '../Wildlife.wmv',
    ),
    array (
        'videoName' => 'WildAnimals',
//        'procedure' => 'myProcedure',
//        'sourceContext' => 'test',
    )
);
echo "upload to vod result: " . json_encode($result) . "\n";

// 上传视频和封面
/*
$result = Vodapi::upload(
    array (
        'videoPath' => './Wildlife.wmv',
        //'coverPath' => './Wildlife-cover.png',
    )
);
echo "upload to vod result: " . json_encode($result) . "\n";
*/