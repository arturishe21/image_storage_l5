<?php namespace Vis\ImageStorage;

trait ConfigurableAPITrait
{
    use ConfigurableTrait;

    //todo refactor this 4 methods
    public function getConfigAPIEnabled()
    {
        return config($this->getConfigNamespace() . '.' . 'video_api.enabled');
    }

    public function getConfigAPICacheMinutes()
    {
        return config($this->getConfigNamespace() . '.' . 'video_api.cache_minutes');
    }

    public function getConfigAPISetData()
    {
        return config($this->getConfigNamespace() . '.' . 'video_api.set_data');
    }

    public function getConfigApiVideoExistenceError()
    {
        return config($this->getConfigNamespace() . '.' . 'video_api.video_existence_error');
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
        return $this->getConfigValue('video_existence_url');
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
