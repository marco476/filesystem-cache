<?php
namespace Service\Cache;

use \Helper\DateHelper;

class CacheItemPool implements CacheItemPoolInterface
{
    //Quaue saved will be elaborate by commit method.
    protected $queueSaved = array();

    //Save the cacheDir by CacheDir::getCacheDir().
    private $cacheDir;

    //CacheItemPool constructor!
    public function __construct()
    {
        $this->cacheDir = CacheDir::getCacheDir();
    }

    //Must return an istance of CacheItem.
    public function getItem($key)
    {
        $cacheItem = new CacheItem();
        $cacheItem->setKey($key);

        if ($this->hasItem($key)) {
            $this->initializeItem($cacheItem);
        }

        return $cacheItem;
    }

    public function getItems(array $keys = array())
    {
        if (empty($keys)) {
            return false;
        }

        $cacheItems = array();

        foreach ($keys as $key) {
            $cacheItems[] = $this->getItem($key);
        }

        return $cacheItems;
    }

    public function hasItem($key)
    {
        return file_exists($this->cacheDir . $key);
    }

    public function clear()
    {
        if (empty($files = glob($this->cacheDir . '*'))) {
            return false;
        }

        foreach ($files as $file) {
            if (!unlink($file)) {
                return false;
            }
        }

        return true;
    }

    public function deleteItem($key)
    {
        return $this->hasItem($key) && unlink($this->cacheDir . $key);
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    public function save(CacheItemInterface $cacheItem)
    {
        if (!$this->isItemValidForSave($cacheItem)) {
            return false;
        }

        $toWrite = '<?php $item=array(\'value\' => ' . var_export($cacheItem->get(), true) . ', \'expire\' => ' . var_export($cacheItem->getExpires(), true) . '); ?>';

        return ($fileCache = fopen($this->cacheDir . $cacheItem->getKey(), 'w')) &&
                fwrite($fileCache, $toWrite) &&
                fclose($fileCache);
    }

    public function saveDeferred(CacheItemInterface $cacheItem)
    {
        return $this->queueSaved[] = $cacheItem;
    }

    public function getQueueSaved()
    {
        return $this->queueSaved;
    }

    public function commit()
    {
        if (empty($this->queueSaved)) {
            return false;
        }

        foreach ($this->queueSaved as $cacheItem) {
            if (!$this->save($cacheItem)) {
                return false;
            }
        }

        return true;
    }

    protected function isItemValidForSave(CacheItemInterface $cacheItem)
    {
        return !$cacheItem->isKeyEmpty() && !$cacheItem->isValueEmpty();
    }

    //Inizialize item if it's in cache.
    //Return true if inizialize in OK, or false if item cache is expire.
    protected function initializeItem(CacheItem $cacheItem)
    {
        $key = $cacheItem->getKey();
        include $this->cacheDir . $key;
        $itemExpire = $item['expire'];
        $expire = $itemExpire === null ? $itemExpire : date_create()->setTimestamp($itemExpire);

        //Check if cache item is valid or not.
        if ($expire !== null && !DateHelper::isDateInFuture($expire)) {
            $this->deleteItem($key);
            return false;
        }

        $cacheItem->set($item['value']);
        if ($expire !== null) {
            $cacheItem->expiresAt($expire);
        }

        return true;
    }
}
