<?php
namespace Service\Cache;

class CacheItemPool implements CacheItemPoolInterface
{
    protected $queueSaved = array();

    //Must return an istance of CacheItem
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
        $cacheItems = array();

        foreach ($keys as $key) {
            $cacheItems[] = $this->getItem($key);
        }

        return $cacheItems;
    }

    public function hasItem($key)
    {
        return file_exists(CacheGlobal::getCacheDir() . $key);
    }

    public function clear()
    {
        if (empty($files = glob(CacheGlobal::getCacheDir() . '*'))) {
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
        return $this->hasItem($key) && unlink(CacheGlobal::getCacheDir() . $key);
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

        return ($fileCache = fopen(CacheGlobal::getCacheDir() . $cacheItem->getKey(), 'w')) &&
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

    protected function initializeItem(CacheItem $cacheItem)
    {
        $key = $cacheItem->getKey();
        include CacheGlobal::getCacheDir() . $key;
        $expire = $item['expire'];
        $expireDate = date_create()->setTimestamp($expire);

        if ($expire === null || $cacheItem->expiresAt($expireDate)) {
            $cacheItem->set($item['value']);
        } else {
            $this->deleteItem($key);
        }
    }
}
