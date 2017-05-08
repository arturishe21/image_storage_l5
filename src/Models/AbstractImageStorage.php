<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractImageStorage extends Model implements ConfigurableInterface, CacheableInterface, FilterableInterface, ChangeableSchemeInterface
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait,

        ConfigurableTrait,
        CacheableTrait,
        FilterableTrait,
        ChangeableSchemeTrait;

    protected $table;
    protected $errorMessage;
    protected $fillable = ['id'];

    public function beforeSaveAction()
    {
        return true;
    }

    public function beforeDeleteAction()
    {
        return true;
    }

    public function afterSaveAction()
    {
        return true;
    }

    public function afterDeleteAction()
    {
        return true;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

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
