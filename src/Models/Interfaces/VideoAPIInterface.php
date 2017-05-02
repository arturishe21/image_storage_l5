<?php namespace Vis\ImageStorage;

interface VideoAPIInterface
{
    public function setVideoId($id);

    public function videoExists();

    public function getPreviewUrl();

    public function getAPIUrl();

    public function getData();
}
