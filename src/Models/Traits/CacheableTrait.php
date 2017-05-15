<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    public static function flushCache()
    {
        $className = static::class;
        $classObject = new $className;

        $cacheTag = $classObject->getConfigNamespace() . '.' . $classObject->getConfigPrefix();

        Cache::tags($cacheTag)->flush();
    }

}
