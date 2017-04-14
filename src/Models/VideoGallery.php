<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;

class VideoGallery extends AbstractImageStorage
{
    protected $table = 'vis_video_galleries';
    protected $configPrefix = 'video_gallery';

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

    public function afterSaveAction()
    {
        $this->makeRelations();
    }

    public function scopeHasVideos($query)
    {
        return $query->has('videos');
    }

    public function scopeHasActiveVideos($query)
    {
        return $query->whereHas('videos', function (\Illuminate\Database\Eloquent\Builder $query) {
            $query->active();
        });
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->byId()->get();

        return $relatedEntities;
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

        self::flushCache();
        Video::flushCache();
    }

    public function changeGalleryOrder($idArray)
    {
        $priority = count($idArray);

        foreach ($idArray as $id) {
            $this->videos()->updateExistingPivot($id, ['priority' => $priority]);
            $priority--;
        }

        self::flushCache();
        Video::flushCache();
    }

    public function deleteToGalleryRelation($id)
    {
        $this->videos()->detach($id);

        self::flushCache();
        Video::flushCache();
    }

    private function makeRelations()
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

    public function relateToGallery($idArray)
    {
        $this->videos()->syncWithoutDetaching($idArray);

        self::flushCache();
        Video::flushCache();
    }

}
