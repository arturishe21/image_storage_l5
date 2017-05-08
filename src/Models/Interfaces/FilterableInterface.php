<?php namespace Vis\ImageStorage;

interface FilterableInterface
{
    public function scopeActive($query);

    public function scopeSlug($query, $slug);

    public function scopeById($query, $order = "desc");

    public function scopeFilterByTitle($query, $title);

    public function scopeFilterByActivity($query, $activity = array());

    public function scopeFilterByDate($query, $date);

    public function scopeFilterByTags($query, $tags = array());

    public function scopeFilterByGalleries($query, $galleries = array());

    public function scopeFilterByVideoGalleries($query, $galleries = array());

    public function scopeFilterSearch($query);
}
