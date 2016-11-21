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

    public function getUrl()
    {

        return route("vis_galleries_show_single", [$this->getSlug(), $this->id]);
    }

    public function getSlug()
    {
        $slug = \Jarboe::urlify($this->title);
        return $slug;
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->byId()->get();

        return $relatedEntities;
    }

    private function getGalleryCurrentPreview(){

        $preview = $this->images()->wherePivot("is_preview", "1")->first();

        return $preview;
    }

    public function getGalleryPreviewImage($size = 'cms_preview'){

        $preview = $this->getGalleryCurrentPreview() ?: $this->images()->first();

        if($preview){
            $image = $preview->getSource($size);
        }else{
            $image = '/packages/vis/image-storage/img/no_image.png';
        }

        return $image;
    }

    public function setPreviewImage($image)
    {
        $currentPreview = $this->getGalleryCurrentPreview();

        if($currentPreview){
            $this->images()->updateExistingPivot($currentPreview->id, ["is_preview" => 0]);
        }

        $this->images()->updateExistingPivot($image, ["is_preview" => 1]);

        self::flushCache();
        Image::flushCache();
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

    public function makeRelations()
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


}
