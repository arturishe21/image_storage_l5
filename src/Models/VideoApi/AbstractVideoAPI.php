<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

//fixme add usage of curlClient package
abstract class AbstractVideoAPI extends Model implements VideoAPIInterface
{
    protected $type;
    protected $videoId;

    public $response;

    protected function getConfigAPI()
    {
        return Config::get('image-storage.video.api');
    }

    protected function getConfigAPIEnabled()
    {
        return $this->getConfigAPI()['enabled'];
    }

    //fixme find better name for this method
    protected function getConfigAPIType()
    {
        return $this->getConfigAPI()[$this->type];
    }

    protected function getConfigAPIExistenceUrl()
    {
        return $this->getConfigAPIType()['video_existence_validation']['check_url'];
    }

    protected function getConfigAPIExistenceError()
    {
        return $this->getConfigAPIType()['video_existence_validation']['error_message'];
    }

    protected function getConfigAPIPreviewUrl()
    {
        return $this->getConfigAPIType()['preview_url'];
    }

    protected function getConfigAPIPreviewQuality()
    {
        return $this->getConfigAPIType()['preview_quality'];
    }

    protected function getConfigAPIURL()
    {
        return $this->getConfigAPIType()['api_url'];
    }

    protected function getConfigAPIKey()
    {
        return $this->getConfigAPIType()['api_key'];
    }

    protected function getEncodedVideoId()
    {
        return urlencode($this->videoId);
    }

    public function setVideoId($id)
    {
        $this->videoId = $id;
    }
}
