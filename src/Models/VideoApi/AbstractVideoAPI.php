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

    public function __construct()
    {
        $this->curl = New CurlClient();
        $this->curl->setRequestHeader([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ]);
    }

    public function setVideoId($id)
    {
        $this->videoId = $id;
    }

    public function getVideoId()
    {
        return $this->videoId;
    }

    public function getApiResponse()
    {
        if (!$this->getConfigAPIEnabled()) {
            return false;
        }

        if (!$this->apiResponse) {
            $tag       = $this->getConfigNamespace() . '.video';
            $cacheName = $this->getConfigPrefix() . "." . $this->getVideoId();
            $minutes   = $this->getConfigAPICacheMinutes();

            $this->apiResponse = Cache::tags($tag)->remember($cacheName, $minutes, function () {
                return $this->requestApiData();
            });
        }

        return $this->apiResponse;
    }

    public function getExistenceErrorMessage()
    {
        $stubs = ["{id}", "{type}"];

        //fixme proper get_class method
        $replacements = [$this->getVideoId(), get_class($this)];

        $message = str_replace($stubs, $replacements, $this->getConfigApiVideoExistenceError());

        return $message;
    }

}
