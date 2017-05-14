<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

use \Vis\CurlClient\CurlClient;

abstract class AbstractVideoAPI extends Model implements VideoAPIInterface, ConfigurableAPIInterface
{
    use ConfigurableAPITrait;

    protected $videoId;
    protected $curl;
    public $apiResponse;

    public function curl()
    {
        if (!$this->curl) {
            $this->curl = New CurlClient();
            $this->curl->setRequestHeader([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json'
            ]);
        }

        return $this->curl;
    }

    public function setVideoId($id)
    {
        $this->videoId = $id;
    }

    public function getVideoId()
    {
        return $this->videoId;
    }

    //fixme add getWatchUrl and getEmbedUrl. getConfigWatchUrl&getConfigEmbedUrl Interface&Trait + do in actual config
    public function getWatchUrl()
    {

    }

    public function getEmbedUrl()
    {

    }

    public function getApiResponse()
    {
        if (!$this->getConfigAPIEnabled()) {
            return false;
        }

        if (!$this->apiResponse) {
            $this->apiResponse = $this->getConfigAPICacheMinutes() === false ? $this->requestApiData() : $this->handleCacheApiResponse();
        }

        return $this->apiResponse;
    }

    public function getExistenceErrorMessage()
    {
        $stubs = ["{id}", "{type}"];

        $replacements = [$this->getVideoId(), class_basename($this)];

        $message = str_replace($stubs, $replacements, $this->getConfigApiVideoExistenceError());

        return $message;
    }

    protected function handleCacheApiResponse()
    {
        $tag       = $this->getConfigNamespace() . '.video';
        $cacheName = $this->getConfigPrefix() . "." . $this->getVideoId();
        $minutes   = $this->getConfigAPICacheMinutes();

        $this->apiResponse = Cache::tags($tag)->remember($cacheName, $minutes, function () {
            return $this->requestApiData();
        });
    }

}
