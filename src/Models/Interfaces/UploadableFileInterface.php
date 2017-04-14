<?php namespace Vis\ImageStorage;

interface UploadableFileInterface
{

    public function setSourceFile($file);

    public function setNewFileData();

    public function replaceSingleFile($size);
}
