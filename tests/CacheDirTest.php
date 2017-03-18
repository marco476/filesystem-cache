<?php
use PHPUnit\Framework\TestCase;
use Service\Cache\CacheDir;

class CacheDirTest extends TestCase
{
    /* ------------------------------------
            setCacheDir METHOD TESTS!
       ----------------------------------- */
    public function testSetCacheDirDefault()
    {
        $expect = $_SERVER["DOCUMENT_ROOT"] . '/cache/';
        CacheDir::setDefaultCacheDir();

        $this->assertEquals($expect, CacheDir::getCacheDir());
    }

    //The relative test for getCacheDir will be the same.
    public function testSetCacheDirNotExist()
    {
        CacheDir::resetCacheDir();

        $this->expectExceptionMessage(CacheDir::NO_VALID_CACHE_DIR);
        CacheDir::setCacheDir('/dirNotExist');
    }

    //The relative test for getCacheDir will be the same.
    public function testSetCacheDirValid()
    {
        CacheDir::resetCacheDir();

        $expect = __DIR__ . '/cacheTest/';
        CacheDir::setCacheDir(__DIR__ . '/cacheTest');

        $this->assertEquals($expect, CacheDir::getCacheDir());
    }

    /* ------------------------------------
            getCacheDir METHOD TESTS!
       ----------------------------------- */
    public function testGetCacheDirDefault()
    {
        CacheDir::resetCacheDir();

        $expect = $_SERVER["DOCUMENT_ROOT"] . '/cache/';
        $this->assertEquals($expect, CacheDir::getCacheDir());
    }
}