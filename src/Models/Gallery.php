<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;



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
        return $this
            ->belongsToMany('Vis\ImageStorage\Image', 'vis_images2galleries', 'id_gallery', 'id_image')
            ->orderBy('priority', 'desc')
            ->withPivot('is_preview');
    } // end images

    public function tags()
    {
        return $this->belongsToMany('Vis\ImageStorage\Tag', 'vis_galleries2tags', 'id_gallery', 'id_tag');
    } // end tags

    public function getUrl(){

        return route("galleries_show_single", [$this->getSlug(), $this->id]);
    }

    public function getSlug(){
        $slug = \Jarboe::urlify($this->title);
        return $slug;
    }

    public function makeGalleryRelations()
    {
        $this->makeGalleryTagsRelations();
    }

    private function makeGalleryTagsRelations()
    {
        $tags = Input::get('relations.image-storage-tags', array());

        $this->tags()->sync($tags);

        self::flushCache();
        Tag::flushCache();
    }

    public function relateImagesToGallery($images)
    {
        $this->images()->syncWithoutDetaching($images);

        self::flushCache();
        Image::flushCache();
    }

    public function getGalleryPreview(){

        return $this->images()->wherePivot("is_preview", "1")->first();
    }

    public function changeGalleryImageOrder($images)
    {
        $priority = count($images);

        foreach ($images as $idImage) {
            $this->images()->updateExistingPivot($idImage, ['priority' => $priority]);
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



    public function setPreviewImage($image)
    {
        $currentPreview = $this->getGalleryPreview();

        if($currentPreview){
            $this->images()->updateExistingPivot($currentPreview->id, ["is_preview" => 0]);
        }

        $this->images()->updateExistingPivot($image, ["is_preview" => 1]);

        self::flushCache();
        Image::flushCache();
    }
}
