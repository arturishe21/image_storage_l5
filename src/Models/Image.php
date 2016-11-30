<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use Vis\Builder\OptmizationImg;


class Image extends AbstractImageStorage
{
    protected $table = 'vis_images';
    protected $configPrefix = 'image';
    protected $imageSizePrefix = 'file_';
    protected $prefixPath = '/storage/image-storage/';

    protected $uploadedImage;

    protected $sourceImage;
    protected $extension;
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

    public function beforeSaveAction(){
        if(!$this->doRenameImageFiles()){
            return false;
        }

        return true;
    }

    public function afterSaveAction(){
        $this->makeRelations();
    }

    public function afterDeleteAction()
    {
        $this->doDeleteImageFiles();
    }

    public function scopeFilterByGalleries($query, $galleries = array())
    {
        if (!$galleries) {
            return $query;
        }
        //fixme переписать под модель
        $table = $this->table;
        $prefix = $this->configPrefix;
        $relatedImagesIds =  \DB::table($table.'2galleries')->whereIn('id_gallery', $galleries)->lists('id_'.$prefix);

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

    public function getSource($size = 'source')
    {
        $field = $this->imageSizePrefix.$size;
        $source = $this->file_folder . $this->$field;

        return $source;
    }



    private function getFileName()
    {
       return $this->getSlug() . "_" . time(). "." . $this->extension;
    }

    public function getConfigSizes()
    {
        return $this->getConfigValue('sizes');
    }

    private function getConfigSizesModifiable()
    {
        $allSizes = $this->getConfigSizes();
        unset($allSizes['source']);
        return $allSizes;
    }

    private function getConfigSizeInfo($size)
    {
        $allSizes = $this->getConfigSizes();
        return $allSizes[$size];
    }

    private function getConfigOptimization()
    {
        return $this->getConfigValue('optimization');
    }

    private function getConfigQuality()
    {
        return $this->getConfigValue('quality');
    }

    private function getConfigUseSourceTitle()
    {
        return $this->getConfigValue('source_title');
    }

    private function getConfigStoreEXIF()
    {
        return $this->getConfigValue('store_exif');
    }

    private function getConfigDeleteFiles()
    {
        return $this->getConfigValue('delete_files');
    }

    private function getConfigRenameFiles()
    {
        return $this->getConfigValue('rename_files');
    }

    private function getConfigImageSizeValidation()
    {
        return $this->getConfigValue('image_size_validation.enabled');
    }

    private function getConfigImageSizeMax()
    {
        return $this->getConfigValue('image_size_validation.max_image_size');
    }

    private function getConfigImageSizeMaxErrorMessage()
    {
        return $this->getConfigValue('image_size_validation.error_message');
    }

    private function getConfigImageExtensionValidation()
    {
        return $this->getConfigValue('image_extension_validation.enabled');
    }

    private function getConfigAllowedImageExtensions()
    {
        return $this->getConfigValue('image_extension_validation.allowed_image_extensions');
    }

    private function getConfigImageExtensionsErrorMessage()
    {
        return $this->getConfigValue('image_extension_validation.error_message');
    }

    private function getPathForFile()
    {
        $postfixPath = date('Y') .'/'. date('m') .'/'. date('d') .'/'. $this->id .'/';

        $chunks = explode("/", $postfixPath);

        return array(
            $chunks,
            $postfixPath
        );
    }

    public function setSourceFile($file)
    {
        if(!$file){
            return false;
        }

        $this->uploadedImage = $file;

       if($this->failsToValidateImage()){
            return false;
        }

        $this->sourceImage = $this->uploadedImage;
        unset($this->uploadedImage);
        return true;
    }

    public function setNewImageData()
    {
        DB::beginTransaction();

        try {
            $this->setImageExifData();
            $this->setImageTitle();
            $this->save();

            $this->doMakeSourceFile();
            $this->doImageVariations();
            $this->save();

            DB::commit();
            return true;

        } catch (Exception $e) {

            DB::rollBack();
            return false;
        }
    }

    private function setImageTitle()
    {
        if($this->getConfigUseSourceTitle()){
            $title = pathinfo($this->sourceImage->getClientOriginalName(), PATHINFO_FILENAME);
        }else{
            $title = md5_file($this->sourceImage->getPathName()) . '_' . time();
        }

        $this->title = $title;
    }

    private function setImageExifData()
    {
        $this->sourceImage->imageData = @(exif_read_data($this->sourceImage, 0, true));

        $this->setExifDate();

        if($this->getConfigStoreEXIF()){
            $this->exif_data = json_encode($this->sourceImage->imageData);
        }
    }

    private function setExifDate()
    {
        if (isset($this->sourceImage->imageData['EXIF']['DateTimesource'])) {
            $this->date_time_source = $this->sourceImage->imageData['EXIF']['DateTimesource'];
        } else {
            $this->date_time_source = "2035-01-01 00:00:00";
        }
    }

    private function failsToValidateImage()
    {
        if($this->failsToValidateImageSize()){
            return true;
        }

        if($this->failsToValidateImageExtension()){
            return true;
        }

        return false;
    }

    private function failsToValidateImageSize()
    {
        if(!$this->getConfigImageSizeValidation()){
            return false;
        }

        $maxImageSize = $this->getConfigImageSizeMax();
        $uploadImageSize = $this->uploadedImage->getClientSize();

        if($uploadImageSize > $maxImageSize){
            $maxImageSizeInMB = $maxImageSize/1000000;
            $message  =  $this->getConfigImageSizeMaxErrorMessage();
            $this->errorMessage =  str_replace("[size]", $maxImageSizeInMB, $message);
            return true;
        }

        return false;
    }

    private function failsToValidateImageExtension()
    {
        if(!$this->getConfigImageExtensionValidation()){
            return false;
        }

        $allowedExtensions = $this->getConfigAllowedImageExtensions();
        $uploadImageExtension = $this->uploadedImage->getClientOriginalExtension();

        if(!in_array($uploadImageExtension,$allowedExtensions)){
            $allowedExtensionsList = implode(",", $allowedExtensions);
            $message  =  $this->getConfigImageExtensionsErrorMessage();
            $this->errorMessage =  str_replace("[extension_list]", $allowedExtensionsList, $message);
            return true;
        }

        return false;
    }

    private function doOptimizeImage($imagePath)
    {
        if ($this->getConfigOptimization()) {
            OptmizationImg::run("/" . $imagePath);
        }
    }

    private function doCheckSchemeSizes()
    {
        $sizes = $this->getConfigSizes();

        foreach ($sizes as $columnName => $sizeInfo) {

            $columnName = $this->imageSizePrefix . $columnName;

            if (!Schema::hasColumn($this->table, $columnName)) {

                Schema::table($this->table, function ($table) use ($columnName) {
                    $table->text($columnName);
                });

            }
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

    private function makeFoldersAndReturnPath($size = 'source')
    {
        $prefixPath = $this->prefixPath;

        list($chunks, $postfixPath) = $this->getPathForFile();

        $chunks[] = $size;

        $tempPath = public_path() . $prefixPath;

        foreach ($chunks as $chunk) {
            $tempPath = $tempPath . $chunk;

            if (!file_exists($tempPath)) {
                if (!mkdir($tempPath, 0755, true)) {
                    throw new \RuntimeException('Unable to create the directory [' . $tempPath . ']');
                }
            }
            $tempPath = $tempPath . '/';
        }

        $destinationPath = $prefixPath . $postfixPath . $size. '/';

        return $destinationPath;
    }

    private function doMakeSourceFile()
    {
        list($chunks, $postfixPath) = $this->getPathForFile();

        $absolutePath =  $this->prefixPath.$postfixPath;

        $this->file_folder = $absolutePath;

        $this->doMakeImage('source');
    }

    private function doMakeImage($size)
    {
        $quality = $this->getConfigQuality();

        $field = $this->imageSizePrefix.$size;

        //fixme can be optimized here
        if($this->sourceImage){
            $sourcePath = $this->sourceImage->getRealPath();
            $this->extension = $this->sourceImage->guessExtension();
        }else{
            $sourcePath = public_path() . $this->getSource();
            $this->extension  = pathinfo($sourcePath, PATHINFO_EXTENSION);
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

        $fileName = $this->getFileName();

        $destinationPath = $this->makeFoldersAndReturnPath($size);

        $path = $destinationPath . $fileName;

        $img->save(public_path() . '/' . $path, $quality);

        $this->$field = $size."/".$fileName;
    }

    private function doImageVariations()
    {
        $this->doCheckSchemeSizes();

        $sizes = $this->getConfigSizesModifiable();
        foreach ($sizes as $size => $sizeInfo) {
            $this->doMakeImage($size);
        }
    }

    private function doDeleteImageFiles()
    {
        if ($this->getConfigDeleteFiles()) {

            //two level protection from removing the public folder
            if($this->file_folder){

                $fileFolder = public_path() . rtrim($this->file_folder, "/");

                if(public_path() != $fileFolder){

                    File::deleteDirectory($fileFolder);
                }
            }
        }
    }

    private function doRenameImageFiles()
    {
        if ($this->getConfigRenameFiles()) {

            if($this->isDirty('title')){

                $sizes = $this->getConfigSizes();

                foreach ($sizes as $sizeName => $sizeInfo) {

                    $imagePath = public_path() . $this->getSource($sizeName);
                    $this->extension  = pathinfo($imagePath, PATHINFO_EXTENSION);

                    $newName = $sizeName ."/".$this->getFileName();
                    $newPath = public_path(). $this->file_folder .  $newName;

                    $field = $this->imageSizePrefix.$sizeName;

                    if(File::move($imagePath,$newPath)){
                        $this->$field = $newName;
                    }
                }
            }
        }
        return true;
    }

    public function replaceSingleImage($size)
    {
        $this->doMakeImage($size);
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

}
