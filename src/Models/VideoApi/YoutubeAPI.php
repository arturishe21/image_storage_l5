<?php namespace Vis\ImageStorage;

class YoutubeAPI extends AbstractVideoAPI
{
    protected $type = 'youtube';

    private function getConfigAPIParts()
    {
        return $this->getConfigAPIType()['api_part'];
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

        $this->response = $youTubeData;

        return true;
    }

}
