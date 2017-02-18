<?php
namespace Service\Cache;

abstract class CacheGlobal
{
    static private $cacheDir;

    //Return the path of cache
    static public function getCacheDir()
    {
        if(self::$cacheDir === null){
            self::$cacheDir = $_SERVER["DOCUMENT_ROOT"] . '/../cache/';
        }

        return self::$cacheDir;
    }
}
