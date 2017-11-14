<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

class ImagesController extends AbstractImageStorageFileController
{
    protected $model = Image::class;

    public function doOptimizeImage()
    {
        $size = Input::get('size');
        $id   = (array) Input::get('id');

        foreach ($id as $key => $value) {
            $image = $this->model->find($value);
            $image->optimizeImage($size);
        }

        return Response::json(array(
            'status' => true,
        ));
    }
}
