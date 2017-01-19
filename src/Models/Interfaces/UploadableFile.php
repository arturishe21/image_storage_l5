<?php namespace Vis\ImageStorage;

interface UploadableFile{

    public function setSourceFile($file);

    public function setNewFileData();

    public function replaceSingleFile($size);
}