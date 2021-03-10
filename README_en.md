![logo](https://main.qcloudimg.com/raw/60f881b8cbc4647af4a29e603e8e6d62.jpg)
## Overview
The VOD SDK for PHP is an SDK for PHP encapsulated based on the upload features of VOD. It provides a rich set of upload capabilities to meet your diversified upload needs. In addition, it encapsulates the APIs of VOD, making it easy for you to integrate the upload capabilities without the need to care about underlying details.

## Features
* [x] General file upload
* [x] HLS file upload
* [x] Upload with cover
* [x] Upload to subapplication
* [x] Upload with task flow
* [x] Upload to specified region
* [x] Upload with temporary key
* [x] Upload with proxy

## Documentation
- [Preparations](https://intl.cloud.tencent.com/document/product/266/33912)
- [API documentation](https://intl.cloud.tencent.com/document/product/266/33916)
- [Feature documentation](https://intl.cloud.tencent.com/document/product/266/33916)
- [Error codes](https://intl.cloud.tencent.com/document/product/266/33916)

## Installation
We recommend you use Composer to install the SDK:
```json
{
    "require": {
        "qcloud/vod-sdk-v5": "v2.4.4"
    }
}
```
If Composer is not used in your project, go to the [GitHub code hosting page](https://github.com/tencentyun/vod-php-sdk-v5/raw/master/packages/vod-sdk.zip) to download the source code package and decompress it into the project.

## Test
The SDK provides a wealth of test cases. You can refer to their call methods. For more information, please see [Test Cases](https://github.com/tencentyun/vod-php-sdk-v5/blob/master/test/VodUploadClientTest.php).
You can view the execution of test cases by running the following command:
```shell
# for windows
.\vendor\bin\phpunit.bat test\VodUploadClientTest.php

# for linux
./vendor/bin/phpunit test/VodUploadClientTest.php
```

## Release Notes
The changes of each version are recorded in the release notes. For more information, please see [Release Notes](https://github.com/tencentyun/vod-php-sdk-v5/releases).

## Contributors
We appreciate the great support of the following developers to the project, and you are welcome to join us.

<a href="https://github.com/xujianguo"><img width=50 height=50 src="https://avatars1.githubusercontent.com/u/7297536?s=60&v=4" /></a><a href="https://github.com/soulhdb"><img width=50 height=50 src="https://avatars3.githubusercontent.com/u/5770953?s=60&v=4" /></a>

## License
[MIT](https://github.com/tencentyun/vod-php-sdk-v5/blob/master/LICENSE)
