## 简介

基于 PHP 语言平台的服务端上传的 SDK，通过 SDK 和配合的 Demo，可以将视频和封面文件直接上传到腾讯云点播系统，同时可以指定各项服务端上传的可选参数。

## 使用方式

1. 下载本 SDK 后，编辑 conf.php，将SECRET_ID 设置为 API 密钥中的 secret id，将 SECRET_KEY 设置为 API 密钥中的 secret key。
1. 执行 php upload_demo.php，即可发起文件上传，上传成功后将获取文件的播放地址和 fileid。
