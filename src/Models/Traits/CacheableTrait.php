<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    protected static function bootCacheableTrait()
    {
        static::saved(function (CacheableInterface $item) {
            $item->flushCache();
        });
        static::deleted(function (CacheableInterface $item) {
            $item->flushCache();
        });
    }

    public function flushCache()
    {
        $cacheTag = $this->getConfigNamespace() . '.' . $this->getConfigPrefix();
        Cache::tags($cacheTag)->flush();
    }

    public function flushCacheRelation(CacheableInterface $relation)
    {
        $this->flushCache();
        $relation->flushCache();
    }

}
