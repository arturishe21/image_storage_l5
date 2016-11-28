<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Tag extends AbstractImageStorage
{
    protected $table = 'vis_tags';
    protected $configPrefix = 'tag';

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-tags')->flush();
    } // end flushCache

    public function images()
    {
        return $this->morphedByMany('Vis\ImageStorage\Image',   'entity', 'vis_tags2entities', 'id_tag', 'id_entity');
    }

    public function videos()
    {
        return $this->morphedByMany('Vis\ImageStorage\Video',   'entity', 'vis_tags2entities', 'id_tag', 'id_entity');
    }

    public function galleries()
    {
        return $this->morphedByMany('Vis\ImageStorage\Gallery', 'entity', 'vis_tags2entities', 'id_tag', 'id_entity');
    }

    public function video_galleries()
    {
        return $this->morphedByMany('Vis\ImageStorage\VideoGallery', 'entity', 'vis_tags2entities', 'id_tag', 'id_entity');
    }

    public function relateImagesToTag($entities)
    {
        $this->images()->syncWithoutDetaching($entities);

        self::flushCache();
        Image::flushCache();
    }

    public function relateVideosToTag($entities)
    {
        $this->videos()->syncWithoutDetaching($entities);

        self::flushCache();
        Video::flushCache();
    }
}
