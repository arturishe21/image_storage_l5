<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class Tag extends AbstractImageStorage
{
    protected $table = 'vis_tags';
    protected $configPrefix = 'tag';
    

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-tags')->flush();
    } // end flushCache

    public function galleries()
    {
        return $this->belongsToMany('Vis\ImageStorage\Gallery', 'vis_galleries2tags', 'id_tag', 'id_gallery');
    } // end tags

    public function images()
    {
        return $this->belongsToMany('Vis\ImageStorage\Image', 'vis_images2tags', 'id_tag', 'id_image');
    } // end tags

    public function relateImagesToTag($images)
    {
        $this->images()->syncWithoutDetaching($images);

        self::flushCache();
        Image::flushCache();
    }
}
