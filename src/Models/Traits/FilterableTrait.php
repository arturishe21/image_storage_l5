<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Session;

trait FilterableTrait
{
    public function scopeActive($query)
    {
        return $query->where('is_active', '1');
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeById($query, $order = "desc")
    {
        return $query->orderBy('id', $order);
    }

    public function scopeFilterByTitle($query, $title)
    {
        if (!$title) {
            return $query;
        }

        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopeFilterByActivity($query, $activity = array())
    {
        if (!$activity) {
            return $query;
        }

        return $query->whereIn('is_active', $activity);
    }

    public function scopeFilterByDate($query, $date)
    {
        if (!$date) {
            return $query;
        }

        $date['from'] = $date['from'] ?: '12-12-1971';
        $date['to']   = $date['to'] ?: '12-12-2222';

        $from = date('Y-m-d 00:00:00', strtotime($date['from']));
        $to   = date('Y-m-d 23:59:59', strtotime($date['to']));

        return $query->whereBetween('created_at', array($from, $to));
    }

    public function scopeFilterByTags($query, $tags = array())
    {
        if (!$tags) {
            return $query;
        }

        $className = get_class($this);

        $relatedId = self::whereHas('tags', function (\Illuminate\Database\Eloquent\Builder $query) use ($tags, $className) {
            $query->whereIn('id_tag', $tags)
                ->where('entity_type', $className);
        })->pluck('id');

        return $query->whereIn('id', $relatedId);
    }

    public function scopeFilterByGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }

        $relatedImagesIds = self::whereHas('galleries', function (\Illuminate\Database\Eloquent\Builder $query) use ($galleries) {
            $query->whereIn('id_gallery', $galleries);
        })->pluck('id');

        return $query->whereIn('id', $relatedImagesIds);
    }

    public function scopeFilterByVideoGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }

        $relatedVideosId = self::whereHas('videoGalleries', function (\Illuminate\Database\Eloquent\Builder $query) use ($galleries) {
            $query->whereIn('id_video_gallery', $galleries);
        })->pluck('id');

        return $query->whereIn('id', $relatedVideosId);
    }

    public function scopeFilterSearch($query)
    {
        $filters = Session::get('image_storage_filter.' . $this->getConfigPrefix(), array());

        foreach ($filters as $column => $value) {
            $query->$column($value);
        }

        return $query;
    }

}