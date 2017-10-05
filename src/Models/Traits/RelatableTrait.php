<?php

namespace Vis\ImageStorage;

trait RelatableTrait
{
    protected $relatableList = ['tags'];

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
                $relatedEntities = (array)request('relations.image-storage-' . $relation);
                $this->$relation()->sync($relatedEntities);
                $relatedClassName::flushCache();
            }
        }
    }

}