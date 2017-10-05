<?php namespace Vis\ImageStorage;

interface CacheableInterface
{
    public static function flushCache();

    public function flushCacheBoth(string $relation);
}
