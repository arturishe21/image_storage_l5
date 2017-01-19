<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class ImagesController extends AbstractImageStorageFileController
{
    protected $model = "Vis\\ImageStorage\\Image";

    public function doOptimizeImage()
    {
        $size = Input::get('size');
        $id   = Input::get('id');

        //fixme weird into array transformation
        if(!is_array($id)){
            $id = explode(" ",$id);
        }

        foreach($id as $key => $value){
            $image = $this->model->find($value);
            $image->optimizeImage($size);
        }

        $this->model->flushCache();

        return Response::json(array(
            'status' => true,
        ));
    }
}
