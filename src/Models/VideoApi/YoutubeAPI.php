<?php namespace Vis\ImageStorage;

class YoutubeAPI extends AbstractVideoAPI
{
    protected $type = 'youtube';

    private function getConfigAPIParts()
    {
        return $this->getConfigAPIType()['api_part'];
    }

    private function getConfigAPIPreviewQuality()
    {
        return $this->getConfigAPIType()['preview_quality'];
    }

    public function videoExists()
    {
        $checkUrl = str_replace("[id_youtube]", $this->getEncodedVideoId(), $this->getConfigAPIExistenceUrl());
        $headers  = get_headers($checkUrl);

        if (!(is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false)) {
            //fixme pass errormessage?
            $this->errorMessage = str_replace("[id_youtube]", $this->getEncodedVideoId(), $this->getConfigAPIExistenceError());
            return false;
        }

        return true;
    }

    public function getPreviewUrl()
    {
        $stubs = ["{id}", "{quality}"];

        $replacements = [$this->getEncodedVideoId(), $this->getConfigAPIPreviewQuality()];

        $url = str_replace($stubs, $replacements, $this->getConfigAPIPreviewUrl());

        return $url;
    }

    public function getAPIUrl()
    {
        $stubs = ["{id}", "{part}", "{key}"];

        $replacements = [$this->getEncodedVideoId(), $this->getConfigAPIParts(), $this->getConfigAPIKey()];

        $url = str_replace($stubs, $replacements, $this->getConfigAPIURL());

        return $url;
    }

    //fixme refactor this method and add Caching
    public function getData()
    {
        if (!$this->getConfigAPIEnabled()) {
            return false;
        }

        $apiResponse = file_get_contents($this->getAPIUrl());
        $apiData = json_decode($apiResponse);

        $youTubeData = array_shift($apiData->items);

        $this->apiResponse = $youTubeData;

        return true;
    }







    //fixme refactor this methods
    private function getSnippet()
    {
        if (!$this->getData()) {
            return false;
        }

        if (!$this->apiResponse->snippet) {
            return false;
        }

        return $this->apiResponse->snippet;
    }

    private function getStatistics()
    {
        if (!$this->getData()) {
            return false;
        }

        if (!$this->apiResponse->statistics) {
            return false;
        }

        return $this->apiResponse->statistics;
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
