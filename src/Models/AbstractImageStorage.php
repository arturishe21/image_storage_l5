<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;


abstract class AbstractImageStorage extends Model
{
    use \Vis\Builder\Helpers\Traits\TranslateTrait,
        \Vis\Builder\Helpers\Traits\SeoTrait;

    protected $configPrefix;
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

    public function getConfigPrefix()
    {
        return $this->configPrefix;
    }

    public function getConfigValue($value)
    {
        return Config::get('image-storage.'.$this->getConfigPrefix().'.'.$value);
    }

    public function getConfigTitle()
    {
        return $this->getConfigValue('title');
    }

    public function getConfigPerPage()
    {
        return $this->getConfigValue('per_page');
    }

    public function getConfigFields()
    {
        return $this->getConfigValue('fields');
    }

    public function getConfigFieldsNames()
    {
        $columnNames = [];

        $configFields = $this->getConfigFields();

        foreach ($configFields as $field => $fieldInfo) {
            if(isset($fieldInfo['tabs'])){
                foreach ($fieldInfo['tabs'] as $tab => $tabInfo) {
                    $columnNames[] = $field.$tabInfo['postfix'];
                }
            }else{
                $columnNames[] = $field;
            }
        }

        return $columnNames;
    }

    private function getUniqueSlug()
    {
        $slug = \Jarboe::urlify($this->title);

        $slugCheck = false;

        while($slugCheck == false){
            $slugCheckQuery = $this->where('slug', 'like', $slug)->where("id", "!=", $this->id)->count();

            if($slugCheckQuery){
                $slug = $slug . "-1";
            }else{
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

        foreach($columnNames as $key=>$columnName) {
            $value =  isset($fields[$columnName]) ? $fields[$columnName] : false;
            $this->$columnName = $value;
        }

        $this->setSlug();
    }

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

    public function scopeFilterByTags($query, $tags = array())
    {
        if (!$tags) {
            return $query;
        }

        $className = get_class($this);

        $relatedId = self::whereHas('tags', function($q)  use ($tags,$className){
            $q->whereIn('id_tag', $tags)
              ->where('entity_type', $className);
        })->lists('id');

        return $query->whereIn('id', $relatedId);
    }

    public function scopeFilterByTitle($query, $title)
    {
        if (!$title) {
            return $query;
        }

        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopeFilterByDate($query, $date)
    {
        if (!$date) {
            return $query;
        }

        $date['from'] = $date['from'] ?: '12-12-1971';
        $date['to'] = $date['to'] ?: '12-12-2222';

        $from = date('Y-m-d 00:00:00', strtotime($date['from']));
        $to = date('Y-m-d 23:59:59', strtotime($date['to']));

        return $query->whereBetween('created_at', array($from, $to));
    } // end scopeByTitle

    public function scopeFilterByActivity($query, $activity  = array())
    {
        if (!$activity) {
            return $query;
        }

        return $query->whereIn('is_active', $activity);
    } // end scopeByTitle

    public function scopeFilterSearch($query)
    {
        $filters = Session::get('image_storage_filter.'.$this->getConfigPrefix(), array());

        foreach($filters as $column => $value) {
            $query->$column($value);
        }

        return $query;
    } // end scopeSearch

    //fixme find a way to use getConfigFieldsNames here
    protected function doCheckSchemeFields()
    {
        $fields = $this->getConfigFields();

        foreach ($fields as $field => $fieldInfo) {
            $columnNames = [];

            if(isset($fieldInfo['tabs'])){
                foreach ($fieldInfo['tabs'] as $tab => $tabInfo) {
                    $columnNames[] = $field.$tabInfo['postfix'];
                }
            }else{
                $columnNames[] = $field;
            }

            foreach($columnNames as $key=>$columnName){
                if (!Schema::hasColumn($this->table, $columnName)) {

                    @list($field, $param) = explode("|", $fieldInfo['field']);

                    Schema::table($this->table, function ($table) use ($columnName, $field, $param) {
                        $field_add = $table->$field($columnName);
                        if ($param) {
                            $field_add->length($param);
                        }
                    });
                }
            }
        }
    }

}
