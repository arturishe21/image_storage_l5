<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Builder;

class Gallery extends AbstractImageStorage
{
    protected $table = 'vis_galleries';
    protected $configPrefix = 'gallery';

    public function images()
    {
        return $this
            ->belongsToMany('Vis\ImageStorage\Image', 'vis_images2galleries', 'id_gallery', 'id_image')
            ->orderBy('priority', 'desc')
            ->withPivot('is_preview');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function scopeHasImages(Builder $query)
    {
        return $query->has('images');
    }

    public function scopeHasActiveImages(Builder $query)
    {
        return $query->whereHas('images', function (Builder $query) {
            $query->active();
        });
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->orderId()->get();

        return $relatedEntities;
    }

    public function getUrl()
    {
        return route("vis_galleries_show_single", [$this->getSlug()]);
    }

    private function getGalleryCurrentPreview()
    {

        $preview = $this->images()->wherePivot("is_preview", "1")->first();

        return $preview;
    }

    public function getGalleryPreviewImage($size = 'cms_preview')
    {

        $preview = $this->getGalleryCurrentPreview() ?: $this->images()->first();

        if ($preview) {
            $image = $preview->getSource($size);
        } else {
            $image = '/packages/vis/image-storage/img/no_image.png';
        }

        return $image;
    }

    public function setPreview($preview)
    {
        $currentPreview = $this->getGalleryCurrentPreview();

        if ($currentPreview) {
            $this->images()->updateExistingPivot($currentPreview->id, ["is_preview" => 0]);
        }

        $this->images()->updateExistingPivot($preview, ["is_preview" => 1]);

        self::flushCache();
        Image::flushCache();
    }

    public function changeGalleryOrder($idArray)
    {
        $priority = count($idArray);

        foreach ($idArray as $id) {
            $this->images()->updateExistingPivot($id, ['priority' => $priority]);
            $priority--;
        }

        self::flushCache();
        Image::flushCache();
    }

    public function deleteToGalleryRelation($id)
    {
        $this->images()->detach($id);

        self::flushCache();
        Image::flushCache();
    }

    public function relateToGallery($idArray)
    {
        $this->images()->syncWithoutDetaching($idArray);

        self::flushCache();
        Image::flushCache();
    }

}
