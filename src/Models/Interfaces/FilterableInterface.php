<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Builder;

interface FilterableInterface
{
    public function scopeActive(Builder $query);

    public function scopeSlug(Builder $query, $slug);

    public function scopeById(Builder $query, $order = "desc");

    public function scopeFilterByTitle(Builder $query, $title);

    public function scopeFilterByActivity(Builder $query, $activity = array());

    public function scopeFilterByDate(Builder $query, $date);

    public function scopeFilterByTags(Builder $query, $tags = array());

    public function scopeFilterByGalleries(Builder $query, $galleries = array());

    public function scopeFilterByVideoGalleries(Builder $query, $galleries = array());

    public function scopeFilterSearch(Builder $query);
}
