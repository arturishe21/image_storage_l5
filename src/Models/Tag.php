<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class Tag extends AbstractClass
{
    protected $table = 'vis_tags';
    protected $configPrefix = 'tag';

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-tags')->flush();
    } // end flushCache

}
