<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Builder;

class VideoGallery extends AbstractImageStorage
{
    protected $table = 'vis_video_galleries';
    protected $configPrefix = 'video_gallery';
    protected $relatableList = ['tags'];

    public function videos()
    {
        return $this
            ->belongsToMany('Vis\ImageStorage\Video', 'vis_videos2video_galleries', 'id_video_gallery', 'id_video')
            ->orderBy('priority', 'desc')
            ->withPivot('is_preview');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function scopeHasVideos($query)
    {
        return $query->has('videos');
    }

    public function scopeHasActiveVideos(Builder $query)
    {
        return $query->whereHas('videos', function (Builder $query) {
            $query->active();
        });
    }

    public function getUrl()
    {
        return route("vis_video_galleries_show_single", [$this->getSlug()]);
    }

    private function getGalleryCurrentPreview()
    {
        $preview = $this->videos()->wherePivot("is_preview", "1")->first();

        return $preview;
    }

    public function setPreview($preview)
    {
        $currentPreview = $this->getGalleryCurrentPreview();

        if ($currentPreview) {
            $this->videos()->updateExistingPivot($currentPreview->id, ["is_preview" => 0]);
        }

        $this->videos()->updateExistingPivot($preview, ["is_preview" => 1]);
        $this->flushCacheBoth('videos');
    }

    public function changeGalleryOrder($idArray)
    {
        $priority = count($idArray);

        foreach ($idArray as $id) {
            $this->videos()->updateExistingPivot($id, ['priority' => $priority]);
            $priority--;
        }
        $this->flushCacheBoth('videos');
    }

    public function deleteToGalleryRelation($id)
    {
        $this->videos()->detach($id);
        $this->flushCacheBoth('videos');
    }

    public function relateToGallery($idArray)
    {
        $this->videos()->syncWithoutDetaching($idArray);
        $this->flushCacheBoth('videos');
    }

}
