<?php

require(__DIR__ . DIRECTORY_SEPARATOR . 'cos-autoloader.php');

$cosClient = new Qcloud\Cos\Client(
                            array(
                            'region' => 'cn-south-2',
                                'credentials'=> array(
                                'appId' => '10022853',
                                'secretId'    => 'AKIDmW5UQRaAzmRvJZsrno14BRpAQVe1Io9V',
                                'secretKey' => 'Ur69B4mKi3ED2snfl9PetdDevCIEzcHl')));
/*
#createBucket
try {
    $result = $cosClient->createBucket(array('Bucket' => 'testbucket'));
    var_dump($result);
    } catch (\Exception $e) {
    echo "$e\n";
}
*/

#uploadbigfile
try {
    $result = $cosClient->upload(
                 $bucket='32d70eabvodgzp1253668508',
                 'repeatedA.txt',
        str_repeat('a', 5* 1024 * 1024));
    var_dump($result);
    } catch (\Exception $e) {
    echo "$e\n";
}

/*
#putObject
try {
    $result = $cosClient->putObject(array(
        'Bucket' => 'testbucket',
        'Key' => '111',
        'Body' => 'Hello World!'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}

#getObject
try {
    $result = $cosClient->getObject(array(
        'Bucket' => 'testbucket',
        'Key' => '111',
        'Body' => 'Hello World!'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}

#deleteObject
try {
    $result = $cosClient->deleteObject(array(
        'Bucket' => 'testbucket',
        'Key' => '111'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}


#deleteBucket
try {
    $result = $cosClient->deleteBucket(array(
        'Bucket' => 'testbucket'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}

#headObject
try {
    $result = $cosClient->headObject(array(
        'Bucket' => 'testbucket',
        'Key' => 'hello.txt'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}

#listObjects
try {
    $result = $cosClient->listObjects(array(
        'Bucket' => 'testbucket'));
    var_dump($result);
} catch (\Exception $e) {
    echo "$e\n";
}


#getObjectUrl
try {
    $bucket =  'testbucket';
    $key = 'hello.txt';
    $region = 'cn-south';
    $url = "/{$key}";
    $request = $cosClient->get($url);
    $signedUrl = $cosClient->getObjectUrl($bucket, $key, '+10 minutes');
    echo ($signedUrl);

} catch (\Exception $e) {
    echo "$e\n";
}
*/

