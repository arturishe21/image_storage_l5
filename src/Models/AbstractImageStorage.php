<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;


abstract class AbstractImageStorage extends Model
{
    protected $configPrefix;
    protected $table;

    public function getConfigPrefix()
    {
        return $this->configPrefix;
    }

    public function getConfigValue($value)
    {
        return Config::get('image-storage.config.'.$this->getConfigPrefix().'.'.$value);
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

    public function scopeActive($query)
    {
        return $query->where('is_active', '1');
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

        //fixme переписать под модель
        $table = $this->table;
        $prefix = $this->configPrefix;
        $relatedImagesIds = \DB::table($table.'2tags')->whereIn('id_tag', $tags)->lists('id_'.$prefix);

        return $query->whereIn('id', $relatedImagesIds);
    } // end scopeByTags

    public function scopeFilterByTitle($query, $title)
    {
        if (!$title) {
            return $query;
        }

        return $query->where('title', 'like', '%'. $title .'%');
    } // end scopeByTitle

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


    public function setFields($fields)
    {
        $this->doCheckSchemeFields();

        $configFields = $this->getConfigFields();

        foreach($configFields as $key=>$value){
            $value = isset($fields[$key]) ? $fields[$key] : false;
            $this->$key = $value;
        }

    }

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

    public function makeRelations()
    {
        return true;
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        return $relatedEntities;
    }

    public function onDeleteAction()
    {
        return true;
    }

}
