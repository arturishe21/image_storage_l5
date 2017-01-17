<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;

class Video extends AbstractImageStorage
{
    protected $table = 'vis_videos';
    protected $configPrefix = 'video';

    private $youTubeData;
    
    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-videos')->flush();
    } // end flushCache

    public function preview()
    {
        return $this->belongsTo('Vis\ImageStorage\Image', 'id_preview');
    }

    public function video_galleries()
    {
        return $this->belongsToMany('Vis\ImageStorage\VideoGallery', 'vis_videos2video_galleries', 'id_video', 'id_video_gallery');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function beforeSaveAction(){

        if($this->failsToValidateVideo()){
            return false;
        }

        return true;
    }

    public function afterSaveAction(){
        $this->makeRelations();
        $this->useYouTubeApi();
    }

    public function scopeFilterByVideoGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }

        $relatedVideosId = self::whereHas('video_galleries', function($q)  use ($galleries){
            $q->whereIn('id_video_gallery', $galleries);
        })->lists('id');

        return $query->whereIn('id', $relatedVideosId);
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->byId()->get();

        $relatedEntities['video_gallery'] = VideoGallery::active()->byId()->get();

        return $relatedEntities;
    }

    public function getUrl()
    {
        return route("vis_videos_show_single", [$this->getSlug()]);
    }

    private function getConfigYouTube()
    {
        return $this->getConfigValue('youtube');
    }

    private function getConfigVideoExistanceValidation()
    {
        return $this->getConfigYouTube()['video_existance_validation'];
    }

    private function getConfigVideoExistanceValidationEnabled()
    {
        return $this->getConfigVideoExistanceValidation()['enabled'];
    }

    private function getConfigVideoExistanceValidationUrl()
    {
        return $this->getConfigVideoExistanceValidation()['check_url'];
    }

    private function getConfigVideoExistanceValidationErrorMessage()
    {
        return $this->getConfigVideoExistanceValidation()['error_message'];
    }

    private function getConfigYouTubeUseApi()
    {
        return $this->getConfigYouTube()['use_api'];
    }

    private function getConfigYouTubeApiURL()
    {
        return $this->getConfigYouTube()['api_url'];
    }

    private function getConfigYouTubeApiKey()
    {
        return $this->getConfigYouTube()['api_key'];
    }

    private function getConfigYouTubeApiPart()
    {
        return $this->getConfigYouTube()['api_part'];
    }

    private function getConfigYouTubeStoreData()
    {
        return $this->getConfigYouTube()['store_data'];
    }

    private function getConfigYouTubeSetData()
    {
        return $this->getConfigYouTube()['set_data'];
    }

    private function getConfigYouTubePreviewUrl()
    {
        return $this->getConfigYouTube()['preview_url'];
    }

    private function getConfigYouTubePreviewQuality()
    {
        return $this->getConfigYouTube()['preview_quality'];
    }

    private function getEncodedYouTubeId()
    {
        return urlencode($this->id_youtube);
    }

    private function getApiUrl()
    {
        $configUrl = $this->getConfigYouTubeApiURL();

        $stubs = ["{id}", "{part}", "{key}"];

        $replacements = [$this->getEncodedYouTubeId(),$this->getConfigYouTubeApiPart(),$this->getConfigYouTubeApiKey()];

        $url = str_replace($stubs,$replacements,$configUrl);

        return $url;
    }

    private function getYouTubeApiData()
    {
        if(!$this->getConfigYouTubeUseApi()){
            return false;
        }

        if(!$this->youTubeData){
            $apiResponse = file_get_contents($this->getApiUrl());
            $apiData = json_decode($apiResponse);

            $youTubeData = array_shift($apiData->items);
            if(!$youTubeData){
                return false;
            }
            $this->youTubeData = $youTubeData;
        }

        return true;
    }

    private function getYouTubeSnippet()
    {
        if(!$this->getYouTubeApiData()){
            return false;
        }

        if(!$this->youTubeData->snippet){
            return false;
        }

        return $this->youTubeData->snippet;
    }

    private function getYouTubeStatistics()
    {
        if(!$this->getYouTubeApiData()){
            return false;
        }

        if(!$this->youTubeData->statistics){
            return false;
        }

        return $this->youTubeData->statistics;
    }

    public function getYouTubeTitle()
    {
        return $this->getYouTubeSnippet() ?  $this->getYouTubeSnippet()->title : "";
    }

    public function getYouTubeDescription()
    {
        return $this->getYouTubeSnippet() ?  $this->getYouTubeSnippet()->description : "";
    }

    public function getYouTubeViewCount()
    {
        return $this->getYouTubeStatistics() ?  $this->getYouTubeStatistics()->viewCount : 0;
    }

    public function getYouTubeLikeCount()
    {
        return $this->getYouTubeStatistics() ?  $this->getYouTubeStatistics()->likeCount : 0;
    }

    public function getYouTubeDislikeCount()
    {
        return $this->getYouTubeStatistics() ?  $this->getYouTubeStatistics()->dislikeCount : 0;
    }

    public function getYouTubeFavoriteCount()
    {
        return $this->getYouTubeStatistics() ?  $this->getYouTubeStatistics()->favoriteCount : 0;
    }

    public function getYouTubeCommentCount()
    {
        return $this->getYouTubeStatistics() ?  $this->getYouTubeStatistics()->commentCount : 0;
    }

    private function getYouTubePreviewUrl()
    {
        $configUrl = $this->getConfigYouTubePreviewUrl();

        $stubs = ["{id}", "{quality}"];

        $replacements = [$this->getEncodedYouTubeId(), $this->getConfigYouTubePreviewQuality()];

        $url = str_replace($stubs,$replacements,$configUrl);

        return $url;
    }

    public function getPreviewImage($size = 'source'){

        if($this->preview){
            $image = $this->preview->getSource($size);
        }else{
            $image = $this->getYouTubePreviewUrl();
        }

        return $image;
    }

    public function setPreviewImage($id){
        $this->preview()->associate($id);
        $this->save();
    }

    public function unsetPreviewImage(){
        $this->preview()->dissociate();
        $this->save();
    }

    private function setYouTubeStoreData()
    {
        if($this->getConfigYouTubeStoreData()){
            $this->youtube_data = json_encode($this->youTubeData);
        }
    }

    private function setYouTubeData()
    {
        if($this->getConfigYouTubeSetData()){

            $columnNames = $this->getConfigFieldsNames();

            foreach($columnNames as $key=>$columnName) {
                if(strpos($columnName, 'title') !== false && !$this->$columnName){
                    $this->$columnName = $this->getYouTubeTitle();
                }else
                if(strpos($columnName, 'description') !== false && !$this->$columnName){
                    $this->$columnName = $this->getYouTubeDescription();
                }
            }

            $this->setSlug();
        }
    }

    private function failsToValidateVideo()
    {
        if($this->failsToValidateVideoExistence()){
            return true;
        }

        return false;
    }

    private function failsToValidateVideoExistence()
    {

        if(!$this->getConfigVideoExistanceValidationEnabled()){
            return false;
        }

        $validationUrl = $this->getConfigVideoExistanceValidationUrl();
        $videoId = $this->id_youtube;

        $checkUrl =  str_replace("[id_youtube]", $videoId, $validationUrl);

        $headers = get_headers($checkUrl);

        if(!(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$headers[0]) : false)){
            $message  =  $this->getConfigVideoExistanceValidationErrorMessage();
            $this->errorMessage =  str_replace("[id_youtube]", $videoId, $message);
            return true;
        }

        return false;
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

        $this->video_galleries()->sync($galleries);

        self::flushCache();
        Gallery::flushCache();
    }

    private function useYouTubeApi()
    {
        if(!$this->getYouTubeApiData()){
            return false;
        }

        $this->setYouTubeStoreData();

        $this->setYouTubeData();

        $this->save();
    }

}
