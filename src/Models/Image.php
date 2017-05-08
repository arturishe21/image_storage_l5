<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Vis\Builder\OptmizationImg;

class Image extends AbstractImageStorageFile
{
    protected $table = 'vis_images';
    protected $configPrefix = 'image';

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

    public function afterSaveAction()
    {
        $this->makeRelations();
    }

    public function getRelatedEntities()
    {
        $relatedEntities = [];

        $relatedEntities['tag'] = Tag::active()->byId()->get();

        $relatedEntities['gallery'] = Gallery::active()->byId()->get();

        $relatedEntities['sizes'] = $this->getConfigSizes();

        return $relatedEntities;
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
        try {
            $this->sourceFile->imageData = (exif_read_data($this->sourceFile, 0, true));
        } catch (\Exception $e) {
            $this->sourceFile->imageData = [];
        }

        $this->setExifDate();

        if ($this->getConfigStoreEXIF()) {
            $this->exif_data = json_encode($this->sourceFile->imageData);
        }
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

    private function makeRelations()
    {
        $this->makeImageTagsRelations();
        $this->makeImageGalleriesRelations();
    }

    private function makeImageTagsRelations()
    {
        $tags = Input::get('relations.image-storage-tags', array());

        $this->tags()->sync($tags);

        self::flushCache();
        Tag::flushCache();
    }

    private function makeImageGalleriesRelations()
    {
        $galleries = Input::get('relations.image-storage-galleries', array());

        $this->galleries()->sync($galleries);

        self::flushCache();
        Gallery::flushCache();
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

    private function doSizesVariations()
    {
        $checkColumns = $this->doCheckSchemeSizes();

        if (count($checkColumns)) {
            $this->updateWithNewSize($checkColumns);
        }

        $sizes = $this->getConfigSizesModifiable();
        foreach ($sizes as $size => $sizeInfo) {
            $this->doMakeFile($size);
        }
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

    private function updateWithNewSize($sizes)
    {
        $images = self::all()->except($this->id);
        foreach ($sizes as $key => $sizeName) {
            foreach ($images as $image) {
                $image->doMakeFile($sizeName);
                $image->save();
            }
        }
    }

}
