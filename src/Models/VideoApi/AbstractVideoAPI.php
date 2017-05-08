<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

use Vis\CurlClient\CurlClient;

abstract class AbstractVideoAPI extends Model implements VideoAPIInterface
{
    protected $cacheNamespace = "image-storage.video.api";
    protected $configPrefix;

    protected $videoId;
    protected $curl;
    public $apiResponse;

    public function __construct()
    {
        $this->curl = New CurlClient();
        $this->curl->setRequestHeader([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ]);
    }

    protected function getConfigAPI()
    {
        return Config::get('image-storage.video.api');
    }

    protected function getConfigAPIEnabled()
    {
        return $this->getConfigAPI()['enabled'];
    }

    protected function getConfigAPICacheMinutes()
    {
        return $this->getConfigAPI()['cache_minutes'];
    }

    public function getConfigAPISetData()
    {
        return $this->getConfigAPI()['set_data'];
    }

    protected function getConfigAPISpecificDetails()
    {
        return $this->getConfigAPI()[$this->getConfigPrefix()];
    }

    protected function getConfigAPIURL()
    {
        return $this->getConfigAPISpecificDetails()['api_url'];
    }

    protected function getConfigAPIKey()
    {
        return $this->getConfigAPISpecificDetails()['api_key'];
    }

    public function setVideoId($id)
    {
        $this->videoId = $id;
    }

    public function getVideoId()
    {
        return $this->videoId;
    }

    public function getConfigPrefix()
    {
        return $this->configPrefix;
    }


    public function getAPIData()
    {
        if (!$this->getConfigAPIEnabled()) {
            return false;
        }

        if (!$this->apiResponse) {
            $this->apiResponse = Cache::tags('image-storage-video')->remember($this->getConfigPrefix() . "-" . $this->getVideoId(), $this->getConfigAPICacheMinutes() , function () {
                return $this->requestApiData();
            });
        }

        return true;
    }

    public function getApiResponse()
    {
        return $this->apiResponse;
    }

    public function getExistenceErrorMessage()
    {
        $stubs = ["{id}", "{type}"];

        $replacements = [$this->getVideoId(), ucfirst($this->getConfigPrefix())];

        $message = str_replace($stubs, $replacements, $this->getConfigAPI()['video_existence_error']);

        return $message;
    }

}
