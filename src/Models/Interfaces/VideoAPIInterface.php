<?php namespace Vis\ImageStorage;

interface VideoAPIInterface
{
    public function setVideoId($id);
    public function getVideoId();

    public function videoExists();

    public function getPreviewUrl();

    public function requestApiData();

    public function getTitle();
    public function getDescription();
    public function getViewCount();
    public function getLikeCount();
    public function getDislikeCount();
    public function getFavoriteCount();
    public function getCommentCount();
}
