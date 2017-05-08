<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;

class Video extends AbstractImageStorage
{
    protected $table = 'vis_videos';
    protected $configPrefix = 'video';

    protected $api;

    //fixme define api types and id_api
    public function api()
    {
        if (!$this->api) {
            $this->api = VideoAPIFactory::makeAPI('youtube');
            $this->api->setVideoId($this->id_youtube);
        }

        return $this->api;
    }

    public function preview()
    {
        return $this->belongsTo('Vis\ImageStorage\Image', 'id_preview');
    }

    public function videoGalleries()
    {
        return $this->belongsToMany('Vis\ImageStorage\VideoGallery', 'vis_videos2video_galleries', 'id_video', 'id_video_gallery');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function beforeSaveAction()
    {
        if (!$this->api()->videoExists()) {
            $this->errorMessage = $this->api()->getExistenceErrorMessage();
            return false;
        }

        return true;
    }

    public function afterSaveAction()
    {
        $this->makeRelations();
        $this->useApiData();
    }

    public function scopeFilterByVideoGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }

        $relatedVideosId = self::whereHas('videoGalleries', function (\Illuminate\Database\Eloquent\Builder $query) use ($galleries) {
            $query->whereIn('id_video_gallery', $galleries);
        })->pluck('id');

        return $query->whereIn('id', $relatedVideosId);
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->byId()->get();

        $relatedEntities['video_gallery'] = VideoGallery::active()->byId()->get();

        return $relatedEntities;
    }

    public function getSource()
    {
        return $this->id_youtube;
    }

    public function getUrl()
    {
        return route("vis_videos_show_single", [$this->getSlug()]);
    }

    public function getPreviewImage($size = 'source')
    {
        if ($this->id_preview) {
            $image = $this->preview->getSource($size);
        } else {
            $image = $this->api()->getPreviewUrl();
        }

        return $image;
    }

    public function setPreviewImage($id)
    {
        $this->preview()->associate($id);
        $this->save();
    }

    public function unsetPreviewImage()
    {
        $this->preview()->dissociate();
        $this->save();
    }

    private function makeRelations()
    {
        $this->makeVideoTagsRelations();
        $this->makeVideoGalleriesRelations();
    }

    private function makeVideoTagsRelations()
    {
        $tags = Input::get('relations.image-storage-tags', array());

        $this->tags()->sync($tags);

        self::flushCache();
        Tag::flushCache();
    }

    private function makeVideoGalleriesRelations()
    {
        $galleries = Input::get('relations.image-storage-galleries', array());

        $this->videoGalleries()->sync($galleries);

        self::flushCache();
        Gallery::flushCache();
    }

    private function useApiData()
    {
        if (!$this->api()->getConfigAPISetData()) {
            return false;
        }

        $columnNames = $this->getConfigFieldsNames();

        foreach ($columnNames as $key => $columnName) {
            if (strpos($columnName, 'title') !== false && !$this->$columnName) {
                $this->$columnName = $this->api()->getTitle();
            } elseif (strpos($columnName, 'description') !== false && !$this->$columnName) {
                $this->$columnName = $this->api()->getDescription();
            }
        }

        $this->setSlug();
        $this->save();
    }
}
