<?php
use PHPUnit\Framework\TestCase;
use Service\Cache\CacheItemPool;
use Service\Cache\CacheDir;
use Service\Cache\CacheItem;

class CacheItemPoolTest extends TestCase
{
    /* ---------------------------------
            getItem METHOD TESTS!
       -------------------------------- */
    public function testGetItemNotInCache()
    {
        $key = 'notInCache';

        $expect = new CacheItem();
        $expect->setKey($key);

        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $itemCache = $itemPool->getItem($key);

        $this->assertEquals($expect, $itemCache);
    }

    public function testGetItemInCacheAndValid()
    {
        $key = 'validItemCache';

        $expectExpire = null;
        $expectValue = array (
            'name' => 'Marco',
            'friends' => array (
                0 => 'Paolo',
                1 => 'Luca',
            ));

        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $itemCache = $itemPool->getItem($key);

        $this->assertEquals($expectExpire, $itemCache->getExpires());
        $this->assertEquals($expectValue, $itemCache->get());
    }

    //Expired cache in the past.
    public function testGetItemInCacheButInvalid()
    {
        $keyOriginal = 'invalidItemCache';
        $keyCopy = 'invalidItemCacheCopy';
        $dirCachePath = __DIR__ . '/cacheTest';

        //I need copy the invalidItemCache file for preserv the test next time.
        $this->assertTrue(copy($dirCachePath . '/' . $keyOriginal, $dirCachePath . '/' . $keyCopy));
        
        $expect = new CacheItem();
        $expect->setKey($keyCopy);

        CacheDir::setCacheDir($dirCachePath);
        $itemPool = new CacheItemPool();
        $itemCache = $itemPool->getItem($keyCopy);

        //The cache file must deleted!
        $this->assertFalse(file_exists($dirCachePath . '/' . $keyCopy));
        $this->assertEquals($expect, $itemCache);
    }

    /* ---------------------------------
            getItems METHOD TESTS!
       -------------------------------- */
    public function testGetItemsArrayParamEmpty()
    {
        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $this->assertFalse($itemPool->getItems(array()));
    }

    public function testGetItemsAllNotInCache()
    {
        $keys = $expectsKeys = array('notInCache1', 'notInCache2');
        $expectsItem = array(new CacheItem(), new CacheItem());

        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $itemsCache = $itemPool->getItems($keys);

        foreach ($itemsCache as $i => $itemCache) {
            $expectsItem[$i]->setKey($expectsKeys[$i]);
            $this->assertEquals($expectsItem[$i], $itemCache);
        }
    }

    public function testGetItemsOnlyOneValidAndInCache()
    {
        $keys = array('validItemCache', 'notInCache');
        $expectsItem = array();

        //First element expected.
        $expectsItem[0] = new CacheItem();
        $expectsItem[0]->setKey($keys[0]);
        $expectsItem[0]->set(array (
            'name' => 'Marco',
            'friends' => array (
                0 => 'Paolo',
                1 => 'Luca',
                )
            )
        );

        //Second element expected.
        $expectsItem[1] = new CacheItem();
        $expectsItem[1]->setKey($keys[1]);

        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $itemsCache = $itemPool->getItems($keys);

        foreach ($itemsCache as $i => $itemCache) {
            $this->assertEquals($expectsItem[$i], $itemCache);
        }
    }

    public function testGetItemsAllValidAndInCache()
    {
        $keys = array('validItemCache', 'validItemCache2');
        $expectsItem = array();

        //First element expected.
        $expectsItem[0] = new CacheItem();
        $expectsItem[0]->setKey($keys[0]);
        $expectsItem[0]->set(array (
            'name' => 'Marco',
            'friends' => array (
                0 => 'Paolo',
                1 => 'Luca',
                )
            )
        );

        //Second element expected.
        $expectsItem[1] = new CacheItem();
        $expectsItem[1]->setKey($keys[1]);
        $expectsItem[1]->set('stringValue');
        $expectsItem[1]->expiresAt(date_create()->setTimestamp(3980916876));

        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $itemsCache = $itemPool->getItems($keys);

        foreach ($itemsCache as $i => $itemCache) {
            $this->assertEquals($expectsItem[$i], $itemCache);
        }
    }

    public function testGetItemsInCacheOneValidAndOneInvalid()
    {
        $dirCachePath = __DIR__ . '/cacheTest';
        $keys = array('validItemCache', 'invalidItemCacheCopy');
        $expectsItem = array();

        //First element expected.
        $expectsItem[0] = new CacheItem();
        $expectsItem[0]->setKey($keys[0]);
        $expectsItem[0]->set(array (
            'name' => 'Marco',
            'friends' => array (
                0 => 'Paolo',
                1 => 'Luca',
                )
            )
        );

        //Second element expected.
        //I need copy the invalidItemCache file for preserv the test next time.
        $this->assertTrue(copy($dirCachePath . '/invalidItemCache', $dirCachePath . '/' . $keys[1]));
        $expectsItem[1] = new CacheItem();
        $expectsItem[1]->setKey($keys[1]);

        CacheDir::setCacheDir($dirCachePath);
        $itemPool = new CacheItemPool($dirCachePath);
        $itemsCache = $itemPool->getItems($keys);

        foreach ($itemsCache as $i => $itemCache) {
            $this->assertEquals($expectsItem[$i], $itemCache);
        }
    }

    public function testGetItemsInCacheAllInvalidAndInCache()
    {
        $dirCachePath = __DIR__ . '/cacheTest';
        $keys = array('invalidItemCacheCopy', 'invalidItemCacheCopy2');
        $expectsItem = array();
        
        //First element expected.
        //I need copy the invalidItemCache file for preserv the test next time.
        $this->assertTrue(copy($dirCachePath . '/invalidItemCache', $dirCachePath . '/' . $keys[0]));
        $expectsItem[0] = new CacheItem();
        $expectsItem[0]->setKey($keys[0]);

        //Second element expected.
        //I need copy the invalidItemCache file for preserv the test next time.
        $this->assertTrue(copy($dirCachePath . '/invalidItemCache2', $dirCachePath . '/' . $keys[1]));
        $expectsItem[1] = new CacheItem();
        $expectsItem[1]->setKey($keys[1]);

        CacheDir::setCacheDir($dirCachePath);
        $itemPool = new CacheItemPool();
        $itemsCache = $itemPool->getItems($keys);

        foreach ($itemsCache as $i => $itemCache) {
            $this->assertEquals($expectsItem[$i], $itemCache);
        }
    }

    /* -------------------------------
            hasItem METHOD TESTS!
       ------------------------------ */
    public function testHasItemTrue()
    {
        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $this->assertTrue($itemPool->hasItem('invalidItemCache'));
    }

    public function testHasItemFalse()
    {
        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $this->assertFalse($itemPool->hasItem('cacheItemNotExist'));
    }

    /* -----------------------------
            clear METHOD TESTS!
       ---------------------------- */
    protected function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    protected function is_dir_empty($dir)
    {
        $handle = opendir($dir);

        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }

        return true;
    }

    
    public function testClearEmptyDirCache()
    {
        CacheDir::setCacheDir(__DIR__ . '/emptyCacheTest');
        $itemPool = new CacheItemPool();
        $this->assertFalse($itemPool->clear());
    }

    public function testClearDirCache()
    {
        //I need copy the directory for preserv it for next test.
        $src = __DIR__ . '/cacheTestToClear';
        $dst =  __DIR__ . '/tmp_cacheTestToClear';
        $this->recurse_copy($src, $dst);

        //The diretory ready for be clear, now is not empty!
        $this->assertFalse($this->is_dir_empty($dst));

        CacheDir::setCacheDir($dst);
        $itemPool = new CacheItemPool();
        $this->assertTrue($itemPool->clear());

        //Now must be empty!
        $this->assertTrue($this->is_dir_empty($dst));
        
        rmdir($dst);
    }

    /* ----------------------------------
            deleteItem METHOD TESTS!
       ---------------------------------- */
    public function testDeleteItemNotInCache()
    {
        CacheDir::setCacheDir(__DIR__ . '/cacheTest');
        $itemPool = new CacheItemPool();
        $this->assertFalse($itemPool->deleteItem('keyNotExist'));
    }

    public function testDeleteItem()
    {
        $dirCachePath = __DIR__ . '/cacheTest';
        $keyCopy = 'validItemCacheCopy';
        //I need copy the validItemCache file for preserv the test next time.
        copy($dirCachePath . '/validItemCache', $dirCachePath . '/' . $keyCopy);

        CacheDir::setCacheDir($dirCachePath);
        $itemPool = new CacheItemPool();
        //Now validItemCacheCopy must existed.
        $this->assertTrue($itemPool->hasItem($keyCopy));
        //Now validItemCacheCopy must deleted.
        $this->assertTrue($itemPool->deleteItem($keyCopy));
        //Now validItemCacheCopy must not existed.
        $this->assertFalse($itemPool->hasItem($keyCopy));
    }

    /* ----------------------------------
            deleteItems METHOD TESTS!
       ---------------------------------- */
}