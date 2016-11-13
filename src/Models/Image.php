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

use Vis\Builder\OptmizationImg;


class Image extends AbstractImageStorage
{
    protected $table = 'vis_images';
    protected $configPrefix = 'image';
    protected $imageSizePrefix = 'file_';
    protected $prefixPath = '/storage/image-storage/';

    protected $uploadedImage;
    protected $errorMessage;

    protected $sourceImage;
    protected $imageData;

    //fixme optimize flushCache
    public static function flushCache()
    {
        Cache::tags('image_storage-images')->flush();
    } // end flushCache

    public function scopePriority($query, $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    } // end priority


    public function getSource($size = 'source')
    {
        $field = $this->imageSizePrefix.$size;
        $source = $this->file_folder . $this->$field;

        return $source;
    } // end getSource

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

    private function getPathByID()
    {
        $id = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $chunks = str_split($id, 2);

        return array(
            $chunks,
            implode('/', $chunks) . '/'
        );
    } // end getPathByID

    private function getFileName()
    {
        $fileName = \Jarboe::urlify($this->title);
        return $fileName;
    }

    public function getUploadErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setSourceFile($file)
    {
        $this->uploadedImage = $file;

        if($this->failsToValidateImage()){
            return false;
        }

        $this->sourceImage = $this->uploadedImage;
        unset($this->uploadedImage);
        return true;
    }

    public function setImageTitle()
    {
        if($this->getConfigUseSourceTitle()){
            $title = pathinfo($this->sourceImage->getClientOriginalName(), PATHINFO_FILENAME);
        }else{
            $title = md5_file($this->sourceImage->getPathName()) . '_' . time();
        }

        $this->title = $title;
    }

    public function setImageData()
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

    public function optimizeImage($size)
    {
        if($size == 'all'){
            $sizes = $this->getConfigSizes();
        }else{
            $sizes = array($size => "");
        }

        foreach ($sizes as $sizeName => $sizeInfo) {
            $imagePath = $this->getSource($sizeName);
            $this->doOptimizeImage($imagePath);
        }
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

    public function makeImageRelations()
    {
        $this->makeImageTagsRelations();
        $this->makeImageGalleriesRelations();
    }

    private function makeImageTagsRelations()
    {
        $data = array();

        foreach (Input::get('relations.image-storage-tags', array()) as $idTag) {
            $data[] = array(
                'id_image' => $this->id,
                'id_tag'   => $idTag
            );
        }
        //fixme переписать под модель
        \DB::table('vis_images2tags')->where('id_image', $this->id)->delete();

        if ($data) {
            \DB::table('vis_images2tags')->insert($data);
        }

        self::flushCache();
        Tag::flushCache();
    }

    private function makeImageGalleriesRelations()
    {
        $data = array();

        foreach (Input::get('relations.image-storage-galleries', array()) as $idGallery) {
            $data[] = array(
                'id_image'     => $this->id,
                'id_gallery'   => $idGallery
            );
        }

        //fixme переписать под модель
        $existingRelations = \DB::table('vis_images2galleries')->where('id_image', $this->id)->get();

        foreach($existingRelations as $key=>$galleryRelation){
            if(!in_array($galleryRelation['id_gallery'],$data)) {
                \DB::table('vis_images2galleries')
                    ->where('id_image', $this->id)
                    ->where('id_gallery', $galleryRelation['id_gallery'])
                    ->delete();
            }
        }

        foreach($data as $key=>$value){
            if(!in_array($value['id_gallery'],$existingRelations)){
                \DB::table('vis_images2galleries')->insert($value);
            }
        }

        self::flushCache();
        Gallery::flushCache();
    }

    private function makeFoldersAndReturnPath($size = 'source')
    {
        $prefixPath = $this->prefixPath;

        list($chunks, $postfixPath) = $this->getPathByID();

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

    public function doMakeSourceFile()
    {
        //fixme find a better way to get absolute path or remove file_folder field
        $absolutePath =  $this->prefixPath.$this->getPathByID()[1];

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
            $extension = $this->sourceImage->guessExtension();
        }else{
            $sourcePath = public_path() . $this->getSource();
            $extension  = pathinfo($sourcePath, PATHINFO_EXTENSION);
        }

        $img = \Image::make($sourcePath);

        $sizeInfo = $this->getConfigSizeInfo($size);

        if(isset($sizeInfo['modify'])){
            foreach ($sizeInfo['modify'] as $method => $args) {
                call_user_func_array(array($img, $method), $args);
                if ($method == 'resizeCanvas' && (isset($args[4]) && preg_match('~rgba\(\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[01]+\.?[0-9]?\)~', $args[4]))) {
                    $extension = 'png';
                }
            }
        }

        $fileName = $this->getFileName() . "_" . time(). "." . $extension;

        $destinationPath = $this->makeFoldersAndReturnPath($size);

        $path = $destinationPath . $fileName;

        $img->save(public_path() . '/' . $path, $quality);

        $this->$field = $size."/".$fileName;
    }

    public function doImageVariations()
    {
        $this->doCheckSchemeSizes();

        $sizes = $this->getConfigSizesModifiable();
        foreach ($sizes as $size => $sizeInfo) {
            $this->doMakeImage($size);
        }
    }

    public function replaceSingleImage($size)
    {
        $this->doMakeImage($size);
    }

    public function doDeleteImageFiles()
    {
        if ($this->getConfigDeleteFiles()) {
            //fixme rtrim
            File::deleteDirectory(public_path() . rtrim($this->file_folder, "/") );
        }
    }
}
