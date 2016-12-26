<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class ImagesController extends AbstractImageStorageController
{
    protected $model = "Vis\\ImageStorage\\Image";

    public function doUploadImage()
    {
        $file = Input::file('image');

        $prefix = $this->model->getConfigPrefix();

        $entity = $this->model;

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        if(!$entity->setNewImageData()) {
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        $html = View::make('image-storage::'. $prefix .'.partials.single_list')->with('entity', $entity)->render();

        $data = array(
            'status' => true,
            'html'   => $html,
            'id'     => $entity->id
        );

        $this->model->flushCache();

        return Response::json($data);
    }

    public function doReplaceSingleImage()
    {
        $file = Input::file('image');
        $size = Input::get('size');
        $id   = Input::get('id');

        $entity = $this->model->find($id);

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        $entity->replaceSingleImage($size);

        $entity->save();

        $this->model->flushCache();

        $data = array(
            'status' => true,
            'src'    => asset($entity->getSource($size)),
        );

        return Response::json($data);

    }

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
