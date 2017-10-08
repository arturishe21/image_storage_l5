<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractImageStorage extends Model implements
    CacheableInterface,
    ChangeableSchemeInterface,
    ConfigurableInterface,
    FilterableInterface,
    RelatableInterface,
    URLableInterface
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait,

        CacheableTrait,
        ChangeableSchemeTrait,
        ConfigurableTrait,
        FilterableTrait,
        RelatableTrait,
        URLableTrait;

    protected $table;
    protected $fillable = ['id'];

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
