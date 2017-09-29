<?php
namespace Vod;

class Conf {
    // Vod php sdk version number.
    const VERSION = 'v5.0.1';

    //log path
    const LOG_PATH = './vod_upload.log';

    /**
     * Get the User-Agent string to send to COS server.
     */
    public static function getUserAgent() {
        return 'vod-php-sdk-' . self::VERSION;
    }
}
