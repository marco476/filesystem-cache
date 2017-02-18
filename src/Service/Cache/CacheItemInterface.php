<?php
namespace Service\Cache;

interface CacheItemInterface
{
    public function getKey();
    public function get();
    public function isHit();
    public function set($value);
    public function expiresAt(\DateTime $expiration);
    public function expiresAfter(string $time);
}
