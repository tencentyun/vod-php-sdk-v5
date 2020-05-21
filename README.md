![logo](https://main.qcloudimg.com/raw/60f881b8cbc4647af4a29e603e8e6d62.jpg)
## 简介
VOD PHP SDK 是基于云点播上传功能进行包装的 PHP 版本 SDK，SDK 中提供丰富的上传能力，满足大部分开发者的上传需求。除此之外，SDK 包含了云点播的 API，开发者在使用上非常方便，不需要关注太多细节即可完成上传功能的对接。

## 功能特性
* [x] 普通文件上传
* [x] HLS文件上传
* [x] 携带封面上传
* [x] 子应用上传
* [x] 上传携带任务流
* [x] 指定上传园区
* [x] 支持使用临时密钥上传
* [x] 设置代理上传

## 文档
- [前置准备工作](https://cloud.tencent.com/document/product/266/9759#.E5.89.8D.E6.8F.90.E6.9D.A1.E4.BB.B6)
- [接口文档](https://cloud.tencent.com/document/product/266/9725#.E6.8E.A5.E5.8F.A3.E6.8F.8F.E8.BF.B0)
- [功能文档](https://cloud.tencent.com/document/product/266/9725#.E7.AE.80.E5.8D.95.E8.A7.86.E9.A2.91.E4.B8.8A.E4.BC.A0)
- [错误码文档](https://cloud.tencent.com/document/product/266/9725#.E9.94.99.E8.AF.AF.E7.A0.81.E8.A1.A8)

## 安装
推荐开发者使用 composer 方式安装 SDK：
```json
{
    "require": {
        "qcloud/vod-sdk-v5": "v2.4.3"
    }
}
```
若项目没有使用到 composer，可以使用源码安装的方式：前往 [Github 代码托管地址](https://github.com/tencentyun/vod-php-sdk-v5/raw/master/packages/vod-sdk.zip) 下载源码压缩包并解压到项目当中。

## 测试
SDK 提供了丰富的测试用例，开发者可以参考示例的调用方式，具体参考：[测试用例](https://github.com/tencentyun/vod-php-sdk-v5/blob/master/test/VodUploadClientTest.php)。
开发者可以通过运行命令查看测试用例的执行情况：
```shell
# for windows
.\vendor\bin\phpunit.bat test\VodUploadClientTest.php

# for linux
./vendor/bin/phpunit test/VodUploadClientTest.php
```

## 变更记录
每个版本的变更细节都记录在日志中，具体请看：[变更日志](https://github.com/tencentyun/vod-php-sdk-v5/releases)。

## 贡献者
感谢以下的开发者对项目的大力支持，欢迎更多的开发者参与进来！

<a href="https://github.com/xujianguo"><img width=50 height=50 src="https://avatars1.githubusercontent.com/u/7297536?s=60&v=4" /></a><a href="https://github.com/soulhdb"><img width=50 height=50 src="https://avatars3.githubusercontent.com/u/5770953?s=60&v=4" /></a>

## 许可证
[MIT](https://github.com/tencentyun/vod-php-sdk-v5/blob/master/LICENSE)
