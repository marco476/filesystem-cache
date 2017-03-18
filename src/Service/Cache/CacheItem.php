<?php
namespace Service\Cache;

use \Helper\DateHelper;

class CacheItem implements CacheItemInterface
{
    protected $key = null;
    protected $value = null;
    protected $expires = null;

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return !$this->isKeyEmpty() && file_exists(CacheDir::getCacheDir() . $this->key);
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function expiresAt(\DateTime $expiration)
    {
        return DateHelper::isDateInFuture($expiration) && $this->expires = $expiration->getTimestamp();
    }

    public function expiresAfter(string $dateIntervalString)
    {
        $futureDate = date_create()->add(\DateInterval::createFromDateString($dateIntervalString));

        return DateHelper::isDateInFuture($futureDate) && $this->expires = $futureDate->getTimestamp();
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function isKeyEmpty()
    {
        return empty($this->key);
    }

    public function isValueEmpty()
    {
        return empty($this->value);
    }
}
