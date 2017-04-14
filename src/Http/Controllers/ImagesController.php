<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

class ImagesController extends AbstractImageStorageFileController
{
    protected $model = "Vis\\ImageStorage\\Image";

    public function doOptimizeImage()
    {
        $size = Input::get('size');
        $id   = (array) Input::get('id');

        foreach ($id as $key => $value) {
            $image = $this->model->find($value);
            $image->optimizeImage($size);
        }

        $this->model->flushCache();

        return Response::json(array(
            'status' => true,
        ));
    }
}
