<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;

trait FilterableTrait
{
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', '1');
    }

    public function scopeSlug(Builder $query, $slug = '')
    {
        if (!$slug) {
            return $query;
        }

        return $query->where('slug', $slug);
    }

    public function scopeById(Builder $query, $order = "desc")
    {
        return $query->orderBy('id', $order);
    }

    public function scopeFilterByTitle(Builder $query, $title = '')
    {
        if (!$title) {
            return $query;
        }

        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopeFilterByActivity(Builder $query, array $activity = [])
    {
        if (!$activity) {
            return $query;
        }

        return $query->whereIn('is_active', $activity);
    }

    public function scopeFilterByDate(Builder $query, array $date = [])
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

    public function scopeFilterByTags(Builder $query, array $tags = [])
    {
        if (!$tags) {
            return $query;
        }

        $className = get_class($this);

        $relatedId = self::whereHas('tags', function (Builder  $query) use ($tags, $className) {
            $query->whereIn('id_tag', $tags)
                ->where('entity_type', $className);
        })->pluck('id');

        return $query->whereIn('id', $relatedId);
    }

    public function scopeFilterSearch(Builder $query)
    {
        $filters = Session::get('image_storage_filter.' . $this->getConfigPrefix(), array());

        foreach ($filters as $column => $value) {
            $query->$column($value);
        }

        return $query;
    }

}
