<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    protected static function bootCacheableTrait()
    {
        static::saved(function (CacheableInterface $item) {
            $item::flushCache();
        });
        static::deleted(function (CacheableInterface $item) {
            $item::flushCache();
        });
    }

    public static function flushCache()
    {
        $className = static::class;
        $classObject = new $className;

        $cacheTag = $classObject->getConfigNamespace() . '.' . $classObject->getConfigPrefix();

        Cache::tags($cacheTag)->flush();
    }

    public function flushCacheBoth(string $relation)
    {
        if (method_exists($this, $relation)) {
            $relatedClass = $this->$relation()->getRelated();
            $relatedClassName = get_class($relatedClass);

            $relatedClassName::flushCache();
            self::flushCache();
        }
    }

}
