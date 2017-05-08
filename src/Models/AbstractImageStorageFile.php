<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\File;

abstract class AbstractImageStorageFile extends AbstractImageStorage implements UploadableFileInterface, ConfigurableFileInterface, ChangeableSchemeFileInterface
{
    use ConfigurableFileTrait,
        ChangeableSchemeFileTrait;

    protected $sizePrefix = 'file_';
    protected $prefixPath = '/storage/image-storage/';

    protected $extension;
    protected $uploadedFile;
    protected $sourceFile;

    public function beforeSaveAction()
    {
        if (!$this->doRenameFiles()) {
            return false;
        }

        return true;
    }

    public function afterDeleteAction()
    {
        $this->doDeleteFiles();
    }

    public function getSource($size = 'source')
    {
        $field = $this->sizePrefix . $size;
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
        $postfixPath = date('Y/m/d', strtotime($this->created_at)) . '/' . $this->id . '/';

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
        if (!$file) {
            return false;
        }

        $this->uploadedFile = $file;

        if ($this->failsToValidateFile()) {
            return false;
        }

        $this->sourceFile = $this->uploadedFile;
        unset($this->uploadedFile);
        return true;
    }

    protected function setFileTitle()
    {
        if ($this->getConfigUseSourceTitle()) {
            $fileName = $this->sourceFile->getClientOriginalName();
            $extension = $this->sourceFile->getClientOriginalExtension();
            $title = strstr($fileName, "." . $extension, true);
        } else {
            $title = md5_file($this->sourceFile->getPathName()) . '_' . time();
        }

        $this->title = $title;
        $this->setSlug();

    }

    private function failsToValidateFile()
    {
        if ($this->failsToValidateFileSize()) {
            return true;
        }

        if ($this->failsToValidateFileExtension()) {
            return true;
        }

        return false;
    }

    private function failsToValidateFileSize()
    {
        if (!$this->getConfigSizeValidation()) {
            return false;
        }

        $maxFileSize = $this->getConfigSizeMax();
        $uploadFileSize = $this->uploadedFile->getClientSize();

        if ($uploadFileSize > $maxFileSize) {
            $maxFileSizeInMB = $maxFileSize / 1000000;
            $message = $this->getConfigSizeMaxErrorMessage();
            $this->errorMessage = str_replace("[size]", $maxFileSizeInMB, $message);
            return true;
        }

        return false;
    }

    private function failsToValidateFileExtension()
    {
        if (!$this->getConfigExtensionValidation()) {
            return false;
        }

        $allowedExtensions = $this->getConfigAllowedExtensions();
        $uploadFileExtension = $this->uploadedFile->getClientOriginalExtension();

        if (!in_array($uploadFileExtension, $allowedExtensions)) {
            $allowedExtensionsList = implode(",", $allowedExtensions);
            $message = $this->getConfigExtensionsErrorMessage();
            $this->errorMessage = str_replace("[extension_list]", $allowedExtensionsList, $message);
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

        $destinationPath = $prefixPath . $postfixPath . $size . '/';

        return $destinationPath;
    }

    protected function setFileFolder()
    {
        $this->file_folder = $this->prefixPath . $this->getPathForFile()[1];
    }

    protected function doDeleteFiles()
    {
        if (!$this->getConfigDeleteFiles()) {
            return false;
        }

        if (!$this->file_folder) {
            return false;
        }

        $fileFolder = public_path() . rtrim($this->file_folder, "/");

        if (public_path() == $fileFolder) {
            return false;
        }

        File::deleteDirectory($fileFolder);

        return true;
    }

    protected function doRenameFiles()
    {
        if (!$this->getConfigRenameFiles()) {
            return true;
        }
        if (!$this->isDirty('title')) {
            return true;
        }

        $sizes = $this->getConfigSizes();

        foreach ($sizes as $sizeName => $sizeInfo) {

            $imagePath = public_path() . $this->getSource($sizeName);

            $this->extension = $this->getFileExtension($sizeName);

            $newName = $sizeName . "/" . $this->makeFileName();
            $newPath = public_path() . $this->file_folder . $newName;

            $field = $this->sizePrefix . $sizeName;

            if (File::move($imagePath, $newPath)) {
                $this->$field = $newName;
            }
        }

        return true;
    }

}
