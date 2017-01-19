<?php namespace Vis\ImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;


abstract class AbstractImageStorageFile extends AbstractImageStorage implements UploadableFile
{
    protected $sizePrefix;
    protected $prefixPath;

    protected $extension;
    protected $uploadedFile;
    protected $sourceFile;

    public function beforeSaveAction(){
        if(!$this->doRenameFiles()){
            return false;
        }

        return true;
    }

    public function afterDeleteAction()
    {
        $this->doDeleteFiles();
    }

    protected function getConfigUseSourceTitle()
    {
        return $this->getConfigValue('source_title');
    }

    protected function getConfigDeleteFiles()
    {
        return $this->getConfigValue('delete_files');
    }

    protected function getConfigRenameFiles()
    {
        return $this->getConfigValue('rename_files');
    }

    protected function getConfigSizeValidation()
    {
        return $this->getConfigValue('size_validation.enabled');
    }

    protected function getConfigSizeMax()
    {
        return $this->getConfigValue('size_validation.max_size');
    }

    protected function getConfigSizeMaxErrorMessage()
    {
        return $this->getConfigValue('size_validation.error_message');
    }

    protected function getConfigExtensionValidation()
    {
        return $this->getConfigValue('extension_validation.enabled');
    }

    protected function getConfigAllowedExtensions()
    {
        return $this->getConfigValue('extension_validation.allowed_extensions');
    }

    protected function getConfigExtensionsErrorMessage()
    {
        return $this->getConfigValue('extension_validation.error_message');
    }

    public function getConfigSizes()
    {
        return $this->getConfigValue('sizes');
    }

    protected function getConfigSizesModifiable()
    {
        $allSizes = $this->getConfigSizes();
        unset($allSizes['source']);
        return $allSizes;
    }

    protected function getConfigSizeInfo($size)
    {
        $allSizes = $this->getConfigSizes();
        return $allSizes[$size];
    }

    public function getSource($size = 'source')
    {
        $field = $this->sizePrefix.$size;
        $source = $this->file_folder . $this->$field;

        return $source;
    }

    public function getFileExtension($size = 'source')
    {
        $path = $this->getSource($size);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension;
    }

    public function getFileName($size = 'source')
    {
        $path = $this->getSource($size);
        $fileName = pathinfo($path, PATHINFO_FILENAME);

        return $fileName;
    }

    public function getFileSize($size = 'source')
    {
        $path = public_path() . $this->getSource($size);
        $size = filesize_format(filesize($path));

        return $size;
    }

    public function getFileMimeType($size = 'source')
    {
        $path = public_path() . $this->getSource($size);
        $size = mime_content_type($path);

        return $size;
    }

    protected function getPathForFile()
    {
        $postfixPath = date('Y/m/d',strtotime($this->created_at)). '/'. $this->id .'/';

        $chunks = explode("/", $postfixPath);

        return array(
            $chunks,
            $postfixPath
        );
    }

    protected function makeFileName()
    {
        return $this->getSlug() . "." . $this->extension;
    }

    public function setSourceFile($file)
    {
        if(!$file){
            return false;
        }

        $this->uploadedFile = $file;

        if($this->failsToValidateFile()){
            return false;
        }

        $this->sourceFile = $this->uploadedFile;
        unset($this->uploadedFile);
        return true;
    }

    protected function setFileTitle()
    {
        if($this->getConfigUseSourceTitle()){
            $fileName = $this->sourceFile->getClientOriginalName();
            $extension = $this->sourceFile->getClientOriginalExtension();
            $title = strstr($fileName, ".".$extension, true);
        }else{
            $title = md5_file($this->sourceFile->getPathName()) . '_' . time();
        }

        $this->title = $title;
        $this->setSlug();

    }

    private function failsToValidateFile()
    {
        if($this->failsToValidateFileSize()){
            return true;
        }

        if($this->failsToValidateFileExtension()){
            return true;
        }

        return false;
    }

    private function failsToValidateFileSize()
    {
        if(!$this->getConfigSizeValidation()){
            return false;
        }

        $maxFileSize = $this->getConfigSizeMax();
        $uploadFileSize = $this->uploadedFile->getClientSize();

        if($uploadFileSize > $maxFileSize){
            $maxFileSizeInMB = $maxFileSize/1000000;
            $message  =  $this->getConfigSizeMaxErrorMessage();
            $this->errorMessage =  str_replace("[size]", $maxFileSizeInMB, $message);
            return true;
        }

        return false;
    }

    private function failsToValidateFileExtension()
    {
        if(!$this->getConfigExtensionValidation()){
            return false;
        }

        $allowedExtensions = $this->getConfigAllowedExtensions();
        $uploadFileExtension = $this->uploadedFile->getClientOriginalExtension();

        if(!in_array($uploadFileExtension,$allowedExtensions)){
            $allowedExtensionsList = implode(",", $allowedExtensions);
            $message  =  $this->getConfigExtensionsErrorMessage();
            $this->errorMessage =  str_replace("[extension_list]", $allowedExtensionsList, $message);
            return true;
        }

        return false;
    }

    protected function doMakeFoldersAndReturnPath($size = 'source')
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

    protected function doMakeSourceFile()
    {
        list($chunks, $postfixPath) = $this->getPathForFile();

        $absolutePath =  $this->prefixPath.$postfixPath;

        $this->file_folder = $absolutePath;

    }

    protected function doDeleteFiles()
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

    protected function doCheckSchemeSizes()
    {
        $sizes = $this->getConfigSizes();

        $newSizes = [];

        foreach ($sizes as $sizeName => $sizeInfo) {

            $columnName = $this->sizePrefix . $sizeName;

            if (!Schema::hasColumn($this->table, $columnName)) {

                Schema::table($this->table, function ($table) use ($columnName) {
                    $table->text($columnName);
                });

                $newSizes[] = $sizeName;
            }
        }

        return $newSizes;
    }

    public function doRenameFiles()
    {
        if ($this->getConfigRenameFiles()) {

            if($this->isDirty('title')){

                $sizes = $this->getConfigSizes();

                foreach ($sizes as $sizeName => $sizeInfo) {

                    $imagePath = public_path() . $this->getSource($sizeName);

                    $this->extension  = $this->getFileExtension($sizeName);

                    $newName = $sizeName ."/".$this->makeFileName();
                    $newPath = public_path(). $this->file_folder .  $newName;

                    $field = $this->sizePrefix.$sizeName;

                    if(File::move($imagePath,$newPath)){
                        $this->$field = $newName;
                    }
                }
            }
        }
        return true;
    }


}
