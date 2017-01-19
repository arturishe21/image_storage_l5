<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Vis\Builder\OptmizationImg;


class Image extends AbstractImageStorageFile
{
    protected $table = 'vis_images';
    protected $configPrefix = 'image';

    protected $sizePrefix = 'file_';
    protected $prefixPath = '/storage/image-storage/';

    protected $imageData;

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-images')->flush();
    } // end flushCache

    public function galleries()
    {
        return $this->belongsToMany('Vis\ImageStorage\Gallery', 'vis_images2galleries', 'id_image', 'id_gallery');
    }

    public function tags()
    {
        return $this->morphToMany('Vis\ImageStorage\Tag', 'entity', 'vis_tags2entities', 'id_entity', 'id_tag');
    }

    public function afterSaveAction(){
        $this->makeRelations();
    }

    public function scopeFilterByGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }

        $relatedImagesIds = self::whereHas('galleries', function($q)  use ($galleries){
                                    $q->whereIn('id_gallery', $galleries);
                                })->lists('id');

        return $query->whereIn('id', $relatedImagesIds);
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

    private function getConfigOptimization()
    {
        return $this->getConfigValue('optimization');
    }

    private function getConfigQuality()
    {
        return $this->getConfigValue('quality');
    }

    private function getConfigStoreEXIF()
    {
        return $this->getConfigValue('store_exif');
    }

    protected function makeFileName()
    {
        return $this->getSlug() . "_" . time(). "." . $this->extension;
    }

    private function setImageExifData()
    {
        $this->sourceFile->imageData = @(exif_read_data($this->sourceFile, 0, true));

        $this->setExifDate();

        if($this->getConfigStoreEXIF()){
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

    private function doMakeFile($size = 'source')
    {
        $quality = $this->getConfigQuality();

        $field = $this->sizePrefix.$size;

        //fixme can be optimized here
        if($this->sourceFile){
            $sourcePath = $this->sourceFile->getRealPath();
            $this->extension = $this->sourceFile->guessExtension();
        }else{
            $sourcePath = public_path() . $this->getSource();
            $this->extension  = $this->getFileExtension($size);
        }

        $img = \Image::make($sourcePath);

        $sizeInfo = $this->getConfigSizeInfo($size);

        if(isset($sizeInfo['modify'])){
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

        $this->$field = $size."/".$fileName;
    }

    private function doSizesVariations()
    {
        $checkColumns = $this->doCheckSchemeSizes();

        if(count($checkColumns)){
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

            $this->doMakeSourceFile();
            $this->doMakeFile();
            $this->doSizesVariations();
            $this->save();

            DB::commit();
            return true;

        } catch (Exception $e) {

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
        //fixme weird way to assign $size
        $sizes = $size ? [$size => ''] : $this->getConfigSizes();

        foreach ($sizes as $sizeName => $sizeInfo) {
            $imagePath = $this->getSource($sizeName);
            $this->doOptimizeImage($imagePath);
        }
    }

    private function updateWithNewSize($sizes)
    {
        $images = self::all()->except($this->id);
        foreach($sizes as $key=>$sizeName) {
            foreach ($images as $image) {
                $image->doMakeFile($sizeName);
                $image->save();
            }
        }
    }

}
