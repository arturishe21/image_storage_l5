<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;

class Video extends AbstractImageStorage
{
    protected $table = 'vis_videos';
    protected $configPrefix = 'video';

    public $api;

    //fixme temp api setter
    public function __construct()
    {
        $this->api = VideoAPIFactory::makeAPI('youtube');
        //fixme temp api id_youtube setter
        $this->api->setVideoId($this->id_youtube);
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
        //fixme temp api id_youtube setter
        $this->api->setVideoId($this->id_youtube);
        if (!$this->api->videoExists()) {
            return false;
        }

        return true;
    }

    public function afterSaveAction()
    {
        $this->makeRelations();
        $this->useAPI();
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

    private function getYouTubeSnippet()
    {
        if (!$this->getYouTubeApiData()) {
            return false;
        }

        if (!$this->youTubeData->snippet) {
            return false;
        }

        return $this->youTubeData->snippet;
    }

    private function getYouTubeStatistics()
    {
        if (!$this->getYouTubeApiData()) {
            return false;
        }

        if (!$this->youTubeData->statistics) {
            return false;
        }

        return $this->youTubeData->statistics;
    }

    public function getYouTubeTitle()
    {
        return $this->getYouTubeSnippet() ? $this->getYouTubeSnippet()->title : "";
    }

    public function getYouTubeDescription()
    {
        return $this->getYouTubeSnippet() ? $this->getYouTubeSnippet()->description : "";
    }

    public function getYouTubeViewCount()
    {
        return $this->getYouTubeStatistics() ? $this->getYouTubeStatistics()->viewCount : 0;
    }

    public function getYouTubeLikeCount()
    {
        return $this->getYouTubeStatistics() ? $this->getYouTubeStatistics()->likeCount : 0;
    }

    public function getYouTubeDislikeCount()
    {
        return $this->getYouTubeStatistics() ? $this->getYouTubeStatistics()->dislikeCount : 0;
    }

    public function getYouTubeFavoriteCount()
    {
        return $this->getYouTubeStatistics() ? $this->getYouTubeStatistics()->favoriteCount : 0;
    }

    public function getYouTubeCommentCount()
    {
        return $this->getYouTubeStatistics() ? $this->getYouTubeStatistics()->commentCount : 0;
    }

    public function getPreviewImage($size = 'source')
    {
        if ($this->id_preview) {
            $image = $this->preview->getSource($size);
        } else {
            //fixme temp api id_youtube setter
            $this->api->setVideoId($this->id_youtube);
            $image = $this->api->getPreviewUrl();
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


    private function getConfigYouTubeSetData()
    {
        return $this->getConfigYouTube()['set_data'];
    }

    private function setYouTubeData()
    {
        if ($this->getConfigYouTubeSetData()) {

            $columnNames = $this->getConfigFieldsNames();

            foreach ($columnNames as $key => $columnName) {
                if (strpos($columnName, 'title') !== false && !$this->$columnName) {
                    $this->$columnName = $this->getYouTubeTitle();
                } else
                    if (strpos($columnName, 'description') !== false && !$this->$columnName) {
                        $this->$columnName = $this->getYouTubeDescription();
                    }
            }

            $this->setSlug();
        }
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

    private function useAPI()
    {
        $this->api->getData();

        dr($this->api->response);
        $this->setYouTubeData();

        $this->save();
    }

}
