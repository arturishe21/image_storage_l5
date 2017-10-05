<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Builder;

//fixme define abstract ImageStorageGallery
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

    public function getUrl()
    {
        return route("vis_galleries_show_single", [$this->getSlug()]);
    }

    private function getGalleryCurrentPreview()
    {
        return $this->images()->wherePivot("is_preview", "1")->first();
    }

    public function getGalleryPreviewImage($size = 'cms_preview')
    {
        $preview = $this->getGalleryCurrentPreview() ?: $this->images()->first();

        return $preview ? $preview->getSource($size) : '/packages/vis/image-storage/img/no_image.png';
    }
    
    public function setPreview($preview)
    {
        if ($currentPreview = $this->getGalleryCurrentPreview()) {
            $this->images()->updateExistingPivot($currentPreview->id, ["is_preview" => 0]);
        }

        $this->images()->updateExistingPivot($preview, ["is_preview" => 1]);
        $this->flushCacheBoth('images');
    }

    public function changeGalleryOrder($idArray)
    {
        $priority = count($idArray);

        foreach ($idArray as $id) {
            $this->images()->updateExistingPivot($id, ['priority' => $priority]);
            $priority--;
        }

        $this->flushCacheBoth('images');
    }

    public function deleteToGalleryRelation($id)
    {
        $this->images()->detach($id);
        $this->flushCacheBoth('images');
    }

    public function relateToGallery($idArray)
    {
        $this->images()->syncWithoutDetaching($idArray);
        $this->flushCacheBoth('images');
    }

}
