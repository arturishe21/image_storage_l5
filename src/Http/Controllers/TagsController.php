<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class TagsController extends Controller
{
    protected $model = "Vis\\ImageStorage\\Tag";

    public function fetchIndex()
    {
        $this->setSearchInput();

        $model = new $this->model;

        $perPage = $model->getConfigPerPage();
        $title = $model->getConfigTitle();

        $data = $model::filterSearch()->orderBy('id', 'DESC')->paginate($perPage);

        if (Request::ajax()) {
            $view = "image-storage::tags.partials.content";
        } else {
            $view = "image-storage::tags.index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data);

    }

    public function doAddImagesToTags()
    {
        $model = $this->model;

        $tags = Input::get('tags', array());
        $images = Input::get('images', array());

        foreach ($tags as $key => $id){
            $entity = $model::find($id);
            $entity->relateImagesToTag($images);
        }

        return Response::json(array(
            'status' => true
        ));

    }

    public function doDeleteTag()
    {
        $id    = Input::get('id');
        $model = new $this->model;

        $image = $model::find($id);

        $image->delete();

        $model::flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function getTagForm()
    {
        $model = new $this->model;

        $id = Input::get('id');

        //fixme should be optimized
        if($id){
            $entity = $model::find($id);
        }else{
            $entity = new $model;
        }

        $fields = $entity->getConfigFields();

        $html = View::make(
            'image-storage::tags.partials.edit_form',
            compact('entity','fields')
        )->render();

        return Response::json(array(
            'status' => true,
            'html' => $html,
        ));
    } // end getImageForm


    public function doSaveTagInfo()
    {
        $model = new $this->model;

        $fields = Input::except('relations');

        //fixme should be optimized
        if($fields['id']){
            $entity = $model::find($fields['id']);
        }else{
            $entity = new $model;
        }

        $entity->setFields($fields);

        $entity->save();

        $entity->makeRelations();

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    } // end doSaveImageInfo

    private function setSearchInput(){
        if(Input::has('image_storage_filter')){
            Session::put('image_storage_filter.tag', Input::get('image_storage_filter', array()));
        }elseif(Input::has('forget_filters')){
            Session::forget('image_storage_filter.tag');
        }
    }
}
