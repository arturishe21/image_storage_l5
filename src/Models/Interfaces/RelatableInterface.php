<?php

namespace Vis\ImageStorage;

interface RelatableInterface
{
    public function getRelatableList(): array;

    public function relationExists($relation): bool;

    public function getRelationClassName($relation): string;
    
    public function getRelatedEntities(): array;


}