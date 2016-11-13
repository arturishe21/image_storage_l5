<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;


class Gallery extends AbstractImageStorage
{
    protected $table = 'vis_galleries';
    protected $configPrefix = 'gallery';

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-galleries')->flush();
    } // end flushCache

    public function images()
    {
        return $this->belongsToMany('Vis\ImageStorage\Image', 'vis_images2galleries', 'id_gallery', 'id_image')->orderBy('priority', 'desc');
    } // end images

    public function tags()
    {
        return $this->belongsToMany('Vis\ImageStorage\Tag', 'vis_galleries2tags', 'id_gallery', 'id_tag');
    } // end tags

    public function relateImagesToGallery($images)
    {
        $this->images()->syncWithoutDetaching($images);

        self::flushCache();
        Image::flushCache();
    }

    public function changeGalleryImageOrder($images)
    {
        $priority = count($images);

        $this->images()->detach();

        foreach ($images as $idImage) {
            $this->images()->attach($idImage, ['priority' => $priority]);
            $priority--;
        }

        self::flushCache();
        Image::flushCache();
    } // end tags

    public function deleteImageGalleryRelation($idImage)
    {
        $this->images()->detach($idImage);

        self::flushCache();
        Image::flushCache();
    } // end tags

}
