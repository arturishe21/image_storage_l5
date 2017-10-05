<?php

namespace Vis\ImageStorage;

trait RelatableTrait
{
    protected $relatableList = [];

    protected static function bootRelatableTrait()
    {
        static::saved(function (RelatableInterface $item) {
            $item->makeRelations();
        });
    }

    public function getRelatableList(): array
    {
        return $this->relatableList;
    }

    public function relationExists($relation): bool
    {
        return method_exists($this, $relation);
    }

    public function getRelationClassName($relation): string
    {
        return get_class($this->$relation()->getRelated());
    }

    public function getRelatedEntities(): array
    {
        $relatedEntities = [];

        foreach ($this->getRelatableList() as $relation) {
            if ($this->relationExists($relation)) {
                $relatedClassName = $this->getRelationClassName($relation);
                $relatedEntities[$relation] = $relatedClassName::active()->orderId()->get();
            }
        }

        return $relatedEntities;
    }

    protected function makeRelations()
    {
        foreach ($this->getRelatableList() as $relation) {
            if ($this->relationExists($relation)) {
                $relatedClassName = $this->getRelationClassName($relation);
                //fixme will clear all relations if they are not passed
                $relatedEntities = (array)request('relations.image-storage-' . $relation);

                if ($this->$relation()->sync($relatedEntities)) {
                    //fixme add to saved method?
                    self::flushCache();
                    $relatedClassName::flushCache();
                }
            }
        }
    }

}