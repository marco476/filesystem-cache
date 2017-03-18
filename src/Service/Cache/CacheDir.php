<?php
namespace Service\Cache;

abstract class CacheDir
{
    const NO_VALID_CACHE_DIR = 'The cache directory setted not exist.';
    protected static $cacheDir;

    //Return the cache path.
    public static function getCacheDir()
    {
        return self::$cacheDir !== null ? self::$cacheDir : self::setDefaultCacheDir();
    }

    //Set cache dir.
    public static function setCacheDir($cacheDir)
    {
        if (empty($cacheDir)) {
            return false;
        }

        $definedCacheDir = substr($cacheDir, -1) == '/' ? $cacheDir : ($cacheDir . '/');
            
        if (!is_dir($cacheDir)) {
            throw new \Exception(self::NO_VALID_CACHE_DIR);
        }

        return self::$cacheDir = $definedCacheDir;
    }

    //Set default cache dir.
    public static function setDefaultCacheDir()
    {
        return self::$cacheDir = $_SERVER["DOCUMENT_ROOT"] . '/cache/';
    }

    //Reset the cache dir setted.
    public static function resetCacheDir()
    {
        self::$cacheDir = null;
    }
}
