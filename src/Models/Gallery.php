<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;


class Gallery extends AbstractClass
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
        return $this->belongsToMany('Vis\ImageStorage\Image', 'vis_images2galleries', 'id_gallery', 'id_image')->orderBy('priority');
    } // end images

    public function tags()
    {
        return $this->belongsToMany('Vis\ImageStorage\Tag', 'vis_galleries2tags', 'id_gallery', 'id_tag');
    } // end tags


    public function changeGalleryImageOrder($images)
    {
        $priority = 1;

        //fixme переписать под модель
        foreach ($images as $idImage) {
            \DB::table('vis_images2galleries')
                ->where('id_image', $idImage)
                ->where('id_gallery', $this->id)
                ->update(array(
                'priority' => $priority
            ));
            $priority++;
        }

        self::flushCache();
        Image::flushCache();
    } // end tags

    public function deleteImageGalleryRelation($idImage)
    {
        //fixme переписать под модель
            \DB::table('vis_images2galleries')
                ->where('id_image', $idImage)
                ->where('id_gallery', $this->id)
                ->delete();

        self::flushCache();
        Image::flushCache();
    } // end tags

}
