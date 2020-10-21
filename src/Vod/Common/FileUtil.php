<?php
/**
 * Created by PhpStorm.
 * User: jianguoxu
 * Date: 2018/11/4
 * Time: 19:18
 */

namespace Vod\Common;

/**
 * File tool class
 *
 * Class FileUtil
 * @package Vod\Common
 */
class FileUtil
{
    /**
     * Get file type
     *
     * @param $filePath
     * @return mixed|string
     */
    public static function getFileType($filePath)
    {
        if (empty($filePath)) {
            return '';
        }
        $tmp = explode("/", $filePath);
        $fullFileName = end($tmp);
        if (strrpos($fullFileName, ".") === false) {
            return '';
        }
        $pathArr = explode(".", $filePath);
        return end($pathArr);
    }

    /**
     * Get filename (excluding file extension)
     *
     * @param $filePath
     * @return bool|mixed|string
     */
    public static function getFileName($filePath)
    {
        if (empty($filePath)) {
            return '';
        }
        $tmp = explode("/", $filePath);
        $fullFileName = end($tmp);
        $pos = strrpos($fullFileName, ".");
        if ($pos === false) {
            return $fullFileName;
        }
        return substr($fullFileName, 0, $pos);
    }

    /**
     * Splice two paths
     *
     * @return string|string[]|null
     */
    public static function joinPath()
    {
        $paths = array();

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }
}
