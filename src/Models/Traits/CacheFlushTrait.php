<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Cache;

trait CacheFlushTrait
{
    public static function flushCache()
    {
        $className = static::class;
        $classObject = new $className;

        $cacheTag = $classObject->getConfigNamespace() . "-" . $classObject->getConfigPrefix();

        Cache::tags($cacheTag)->flush();
    } // end flushCache

}