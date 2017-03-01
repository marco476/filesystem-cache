<?php
namespace Service\Cache;

abstract class CacheGlobal
{
    const NO_VALID_CACHE_DIR = 'The cache directory setted not exist.';

    private static $cacheDir;

    //Return the cache path
    public static function getCacheDir()
    {
        return self::$cacheDir !== null ? self::$cacheDir : self::setCacheDir();
    }

    //Set the cache path
    public static function setCacheDir($cacheDir = false)
    {
        $definedCacheDir = $_SERVER["DOCUMENT_ROOT"] . '/cache/';

        if (!empty($cacheDir)) {
            $cacheDir = substr($cacheDir, -1) == '/' ? $cacheDir : ($cacheDir . '/');
            
            if (!is_dir($cacheDir)) {
                throw new Exception(self::NO_VALID_CACHE_DIR);
            }

            $definedCacheDir = $cacheDir;
        }

        return self::$cacheDir = $definedCacheDir;
    }
}
