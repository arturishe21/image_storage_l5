<?php namespace Vis\ImageStorage;

trait ConfigurableAPITrait
{
    use ConfigurableTrait;

    //fixme
    public function getConfigAPIEnabled()
    {
        return config('image-storage.video.api.enabled');
    }
    //fixme
    public function getConfigAPICacheMinutes()
    {
        return config('image-storage.video.api.cache_minutes');
    }
    //fixme
    public function getConfigAPISetData()
    {
        return config('image-storage.video.api.set_data');
    }

    //fixme
    public function getConfigApiVideoExistenceError()
    {
        return config('image-storage.video.api.video_existence_error');
    }

    public function getConfigAPIURL()
    {
        return $this->getConfigValue('api_url');
    }

    public function getConfigAPIKey()
    {
        return $this->getConfigValue('api_key');
    }

    public function getConfigAPIParts()
    {
        return $this->getConfigValue('api_part');
    }

    public function getConfigAPIExistenceUrl()
    {
        return $this->getConfigValue('video_check_url');
    }

    public function getConfigAPIPreviewUrl()
    {
        return $this->getConfigValue('preview_url');
    }

    public function getConfigAPIPreviewQuality()
    {
        return $this->getConfigValue('preview_quality');
    }

}
