<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class ImagesController extends Controller
{
    protected $model = "Vis\\ImageStorage\\Image";

    public function fetchIndex()
    {
        $this->setSearchInput();

        $model = new $this->model;

        $perPage = $model->getConfigPerPage();
        $title = $model->getConfigTitle();

        $data = $model::filterSearch()->byId()->limit($perPage)->get();

        $galleries = Gallery::active()->byId()->get();
        $tags = Tag::active()->byId()->get();

        if (Request::ajax()) {
            $view = "image-storage::images.partials.content";
        } else {
            $view = "image-storage::images.index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data)
            ->with('galleries', $galleries)
            ->with('tags', $tags);
    }

    public function doSearchImages()
    {
        $this->setSearchInput();

        $model = new $this->model;
        $perPage = $model->getConfigPerPage();

        $images = $model::filterSearch()
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get();

        $html = '';
        foreach ($images as $image) {
            $html .= View::make('image-storage::images.partials.list_image')->with('image', $image)->render();
        }

        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    }

    public function doLoadMoreImages()
    {
        $model = new $this->model;
        $page = Input::get('page');

        $perPage = $model->getConfigPerPage();
        $images = $model::filterSearch()->orderBy('id', 'desc')->skip($perPage * $page)->limit($perPage)->get();
        $html = '';
        foreach ($images as $image) {
            $html .= View::make('image-storage::images.partials.list_image')->with('image', $image)->render();
        }
        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    } // end doLoadMoreImages

    public function doUploadImage()
    {
        $model = $this->model;

        $file = Input::file('image');

        $entity = new $model;

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getUploadErrorMessage() ));
        }

        $entity->setImageData();
        $entity->setImageTitle();
        $entity->save();

        $entity->doMakeSourceFile();
        $entity->doImageVariations();
        $entity->save();

        $html = View::make('image-storage::images.partials.list_image')->with('image', $entity)->render();

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
        $model = new $this->model;

        $file = Input::file('image');
        $size = Input::get('size');
        $id   = Input::get('id');

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

    public function getImageForm()
    {
        $model = new $this->model;
        $id   = Input::get('id');

        $entity = $model::find($id);

        $sizes = $model->getConfigSizes();

        $fields = $model->getConfigFields();

        $galleries = Gallery::active()->byId()->get();
        $tags = Tag::active()->byId()->get();

        $html = View::make(
            'image-storage::images.partials.edit_form',
            compact(
                'entity',
                'sizes',
                'galleries',
                'tags',
                'fields'
            )
        )->render();

        return Response::json(array(
            'status' => true,
            'html'   => $html,
        ));
    } // end getImageForm

    public function doDeleteImage()
    {
        $id    = Input::get('id');
        $model = new $this->model;

        $image = $model::find($id);

        $image->doDeleteImageFiles();

        $image->delete();

        $model::flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function doSaveImageInfo()
    {
        $model = new $this->model;

        $fields = Input::except('relations');

        $image = $model::find($fields['id']);

        $image->setFields($fields);

        $image->save();

        $image->makeRelations();

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    } // end doSaveImageInfo

    public function doOptimizeImage()
    {

        $model = new $this->model;

        $size = Input::get('size');
        $id   = Input::get('id');

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

    //fixme optimize search inputs
    private function setSearchInput(){
        if(Input::has('image_storage_filter')){
            Session::put('image_storage_filter.image', Input::get('image_storage_filter', array()));
        }elseif(Input::has('forget_filters')){
            Session::forget('image_storage_filter.image');
        }
    }
}
