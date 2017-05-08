<?php namespace Vis\ImageStorage;

class YoutubeAPI extends AbstractVideoAPI
{
    protected $configPrefix = 'video.api.youtube';

    public function videoExists()
    {
        $url = $this->getConfigAPIExistenceUrl();

        $queryParams = [
            'format' => 'json',
            'url'    => 'http://www.youtube.com/watch?v=' . $this->getVideoId()
        ];

        $this->curl->setRequestUrl($url, $queryParams)->doCurlRequest();

        if (!$this->curl->isSuccessful()) {
            return false;
        }

        return true;
    }

    public function getPreviewUrl()
    {
        $stubs = ["{id}", "{quality}"];

        $replacements = [$this->getVideoId(), $this->getConfigAPIPreviewQuality()];

        $url = str_replace($stubs, $replacements, $this->getConfigAPIPreviewUrl());

        return $url;
    }

    public function requestApiData(){

        $queryParams = [
            'id'    => $this->getVideoId(),
            'part'  => $this->getConfigAPIParts(),
            'key'   => $this->getConfigAPIKey()
        ];

        $this->curl->setRequestUrl($this->getConfigAPIURL(), $queryParams)->doCurlRequest();

        if (!$this->curl->isSuccessful()) {
            return false;
        }

        $apiData = json_decode($this->curl->getCurlResponseBody());

        return array_shift($apiData->items);
    }

    private function getSnippet()
    {
        if (!$this->getAPIData()) {
            return false;
        }

        if (!$this->getApiResponse()->snippet) {
            return false;
        }

        return $this->getApiResponse()->snippet;
    }

    private function getStatistics()
    {
        if (!$this->getAPIData()) {
            return false;
        }

        if (!$this->getApiResponse()->statistics) {
            return false;
        }

        return $this->getApiResponse()->statistics;
    }

    public function getTitle()
    {
        return $this->getSnippet() ? $this->getSnippet()->title : "";
    }

    public function getDescription()
    {
        return $this->getSnippet() ? $this->getSnippet()->description : "";
    }

    public function getViewCount()
    {
        return $this->getStatistics() ? $this->getStatistics()->viewCount : 0;
    }

    public function getLikeCount()
    {
        return $this->getStatistics() ? $this->getStatistics()->likeCount : 0;
    }

    public function getDislikeCount()
    {
        return $this->getStatistics() ? $this->getStatistics()->dislikeCount : 0;
    }

    public function getFavoriteCount()
    {
        return $this->getStatistics() ? $this->getStatistics()->favoriteCount : 0;
    }

    public function getCommentCount()
    {
        return $this->getStatistics() ? $this->getStatistics()->commentCount : 0;
    }
}
