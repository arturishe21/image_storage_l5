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

    public function doLoadMoreImages()
    {
        $page = Input::get('page');

        $model = new $this->model;
        $perPage = $model->getConfigPerPage();
        $prefix = $model->getConfigPrefix();

        $images = $model::filterSearch()->orderBy('id', 'desc')->skip($perPage * $page)->limit($perPage)->get();
        $html = '';
        foreach ($images as $image) {
            $html .= View::make('image-storage::'.$prefix.'.partials.list_image')->with('image', $image)->render();
        }
        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    } // end doLoadMoreImages

    public function doUploadImage()
    {
        $file = Input::file('image');

        $model = $this->model;
        $entity = new $model;
        $prefix = $model->getConfigPrefix();

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getUploadErrorMessage() ));
        }

        $entity->setImageData();
        $entity->setImageTitle();
        $entity->save();

        $entity->doMakeSourceFile();
        $entity->doImageVariations();
        $entity->save();

        $html = View::make('image-storage::'. $prefix .'.partials.list_image')->with('image', $entity)->render();

        $model::flushCache();

        $data = array(
            'status' => true,
            'html'   => $html,
            'id'     => $entity->id
        );

        return Response::json($data);
    } // end doUploadImage

    public function doReplaceSingleImage()
    {

        $file = Input::file('image');
        $size = Input::get('size');
        $id   = Input::get('id');

        $model = new $this->model;

        $entity = $model::find($id);

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getUploadErrorMessage() ));
        }

        $entity->replaceSingleImage($size);

        $entity->save();

        $entity::flushCache();

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

        $model = new $this->model;

        //fixme weird into array transformation
        if(!is_array($id)){
            $id = explode(" ",$id);
        }

        foreach($id as $key => $value){
            $image = $model::find($value);
            $image->optimizeImage($size);
        }

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    }
}
