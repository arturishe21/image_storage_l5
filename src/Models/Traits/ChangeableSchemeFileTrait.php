<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Schema;
use \Illuminate\Database\Schema\Blueprint;

trait ChangeableSchemeFileTrait
{
    public function doCheckSchemeSizes()
    {
        $sizes = $this->getConfigSizes();

        $newSizes = [];

        foreach ($sizes as $sizeName => $sizeInfo) {

            $columnName = $this->sizePrefix . $sizeName;

            if (!Schema::hasColumn($this->table, $columnName)) {

                Schema::table($this->table, function (Blueprint $table) use ($columnName) {
                    $table->text($columnName);
                });

                $newSizes[] = $sizeName;
            }
        }

        return $newSizes;
    }
}
