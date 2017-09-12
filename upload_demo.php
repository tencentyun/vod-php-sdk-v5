<?php
require_once './vod_api.php';

// 上传视频
$result = Vodapi::upload(
    array (
        'videoPath' => './Wildlife.wmv',
    ),
    array (
        'videoName' => 'WildAnimals',
        'procedure' => 'myProcedure',
        'sourceContext' => 'test',
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
