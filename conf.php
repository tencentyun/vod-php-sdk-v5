<?php

class Conf {
    // Vod php sdk version number.
    const VERSION = 'v5.0.1';
    // Please refer to http://console.qcloud.com to fetch your secret_id and secret_key.

    const SECRET_ID = 'AKIDmW5xxxxxxxxxxxxxxx1Io9V';
    const SECRET_KEY = 'Ur69B4xxxxxxxxxxxxxxzcHl';

    //log path
    const LOG_PATH = './vod_upload.log';

    /**
     * Get the User-Agent string to send to COS server.
     */
    public static function getUserAgent() {
        return 'vod-php-sdk-' . self::VERSION;
    }
}
