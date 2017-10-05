<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Vis\Builder\OptmizationImg;

class Image extends AbstractImageStorageFile
{
    protected $table = 'vis_images';
    protected $configPrefix = 'image';
    protected $relatableList = ['galleries', 'tags'];

    protected $imageData;
    protected $sourcePath;

    public function galleries()
    {
        return $this->belongsToMany('Vis\ImageStorage\Gallery', 'vis_images2galleries', 'id_image', 'id_gallery');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function scopeFilterByGalleries(Builder $query, array $galleries = [])
    {
        if (!$galleries) {
            return $query;
        }

        $relatedImagesIds = self::whereHas('galleries', function (Builder $query) use ($galleries) {
            $query->whereIn('id_gallery', $galleries);
        })->pluck('id');

        return $query->whereIn('id', $relatedImagesIds);
    }

    public function getUrl()
    {
        return route("vis_images_show_single", [$this->getSlug()]);
    }

    protected function makeFileName()
    {
        return $this->getSlug() . "_" . time() . "." . $this->extension;
    }

    private function setImageExifData()
    {
        if (!$this->getConfigStoreEXIF()) {
            return false;
        }

        try {
            $this->sourceFile->imageData = exif_read_data($this->sourceFile, 0, true);
        } catch (\Exception $e) {
            $this->sourceFile->imageData = [];
        }

        $this->setExifDate();

        $this->exif_data = json_encode($this->sourceFile->imageData);

        return true;
    }

    private function setExifDate()
    {
        if (isset($this->sourceFile->imageData['EXIF']['DateTimesource'])) {
            $this->date_time_source = $this->sourceFile->imageData['EXIF']['DateTimesource'];
        } else {
            $this->date_time_source = "2035-01-01 00:00:00";
        }
    }

    private function doOptimizeImage($imagePath)
    {
        if ($this->getConfigOptimization()) {
            OptmizationImg::run("/" . $imagePath);
        }
    }

    private function setExtension($size)
    {
        if ($this->sourceFile) {
            $extension = $this->sourceFile->guessExtension();
        } else {
            $extension = $this->getFileExtension($size) ?: $this->getFileExtension();
        }

        $this->extension = $extension;
    }

    private function setSourcePath()
    {
        if ($this->sourceFile) {
            $sourcePath = $this->sourceFile->getRealPath();
        } else {
            $sourcePath = public_path() . $this->getSource();
        }

        $this->sourcePath = $sourcePath;
    }

    private function doMakeFile($size = 'source')
    {
        $quality = $this->getConfigQuality();

        $field = $this->sizePrefix . $size;

        $this->setExtension($size);
        $this->setSourcePath();

        $img = \Image::make($this->sourcePath);

        $sizeInfo = $this->getConfigSizeInfo($size);

        if (isset($sizeInfo['modify'])) {
            foreach ($sizeInfo['modify'] as $method => $args) {
                call_user_func_array(array($img, $method), $args);
                if ($method == 'resizeCanvas' && (isset($args[4]) && preg_match('~rgba\(\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[01]+\.?[0-9]?\)~', $args[4]))) {
                    $this->extension = 'png';
                }
            }
        }

        $fileName = $this->makeFileName();

        $destinationPath = $this->doMakeFoldersAndReturnPath($size);

        $path = $destinationPath . $fileName;

        $img->save(public_path() . '/' . $path, $quality);

        $this->$field = $size . "/" . $fileName;
    }

    public function setNewFileData()
    {
        DB::beginTransaction();

        try {
            $this->setImageExifData();
            $this->setFileTitle();
            $this->save();

            $this->setFileFolder();
            $this->doMakeFile();
            $this->doSizesVariations();
            $this->save();

            DB::commit();
            return true;

        } catch (\Exception $e) {

            DB::rollBack();
            return false;
        }
    }

    protected function doSizeVariation($sizeName)
    {
        $this->doMakeFile($sizeName);
    }

    public function replaceSingleFile($size)
    {
        $this->doMakeFile($size);
    }

    public function optimizeImage($size)
    {
        $sizes = $size ? [$size => ''] : $this->getConfigSizes();

        foreach ($sizes as $sizeName => $sizeInfo) {
            $imagePath = $this->getSource($sizeName);
            $this->doOptimizeImage($imagePath);
        }
    }



}
