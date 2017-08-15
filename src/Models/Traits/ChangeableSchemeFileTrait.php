<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Schema\Blueprint;

trait ChangeableSchemeFileTrait
{
    public function doCheckSchemeSizes()
    {
        $sizes = $this->getConfigSizes();

        foreach ($sizes as $sizeName => $sizeInfo) {

            $columnName = $this->sizePrefix . $sizeName;

            if (!Schema::hasColumn($this->table, $columnName)) {

                Schema::table($this->table, function (Blueprint $table) use ($columnName) {
                    $table->text($columnName);
                });
            }
        }
    }

    public function doUpdateSizes()
    {
        $sizes = $this->getConfigSizesModifiable();

        foreach ($sizes as $sizeName => $sizeInfo) {

            $columnName = $this->sizePrefix . $sizeName;

            $entities = $this->where($columnName, '=', '')->get();

            foreach ($entities as $key => $entity) {
                $entity->doSizeVariation($sizeName);
                $entity->save();
            }
        }
    }
}
