<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

abstract class AbstractImageStorage extends Model implements CacheableInterface, ChangeableSchemeInterface, ConfigurableInterface, FilterableInterface
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait,

        CacheableTrait,
        ChangeableSchemeTrait,
        ConfigurableTrait,
        FilterableTrait;

    protected $table;
    protected $fillable = ['id'];

    protected static function boot()
    {
        parent::boot();

        //fixme move this to trait
        static::saved(function (AbstractImageStorage $item) {
            $item->makeRelations();
        });
    }

    //fixme move to errorTrait

    protected $errorMessage;

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    //use this function instead of simple assign
    public function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    protected function makeRelations()
    {
        //fixme this should be defines as model property ?
        $relations = ['documents', 'galleries', 'images', 'videos', 'videoGalleries', 'tags'];

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                //fixme will clear all relations if they are not passed
                $relatedEntities = (array)Input::get('relations.image-storage-' . $relation);
                $relatedClassName = get_class($this->$relation()->getRelated());

                if ($this->$relation()->sync($relatedEntities)) {
                    //fixme add to saved method?
                    self::flushCache();
                    $relatedClassName::flushCache();
                }
            }
        }
    }

    //fixme refactor this method
    public function getRelatedEntities()
    {
        $relatedEntities = [];

        return $relatedEntities;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    private function getUniqueSlug()
    {
        $slug = \Jarboe::urlify($this->title);

        $slugCheck = false;

        while ($slugCheck === false) {
            $slugCheckQuery = $this->where('slug', 'like', $slug)->where("id", "!=", $this->id)->count();

            if ($slugCheckQuery) {
                $slug = $slug . "-1";
            } else {
                $slugCheck = true;
            }
        }

        return $slug;
    }

    //fixme think about extracting this method to boot
    public function setSlug()
    {
        $this->slug = $this->getUniqueSlug();
    }

    public function setFields($fields)
    {
        $this->doCheckSchemeFields();

        $columnNames = $this->getConfigFieldsNames();

        foreach ($columnNames as $key => $columnName) {
            $value = isset($fields[$columnName]) ? $fields[$columnName] : false;
            $this->$columnName = $value;
        }

        $this->setSlug();
    }

}
