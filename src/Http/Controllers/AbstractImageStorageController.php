<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

abstract class AbstractImageStorageController extends Controller
{
    protected $model;

    public function fetchIndex()
    {
        $this->setSearchInput();

        $model = new $this->model;

        $perPage = $model->getConfigPerPage();
        $title = $model->getConfigTitle();
        $prefix = $model->getConfigPrefix();
        $relatedEntities = $model->getRelatedEntities();

        $data = $model::filterSearch()->orderBy('id', 'DESC')->paginate($perPage);

        if (Request::ajax()) {
            $view = "image-storage::". $prefix .".partials.content";
        } else {
            $view = "image-storage::". $prefix .".index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data)
            ->with('relatedEntities', $relatedEntities);
    }

    public function doDelete()
    {
        $id    = Input::get('id');
        $model = new $this->model;

        $entity = $model::find($id);

        $entity->onDeleteAction();

        $entity->delete();

        $model::flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function getForm()
    {
        $id = Input::get('id');

        $model = new $this->model;
        $prefix = $model->getConfigPrefix();
        $relatedEntities = $model->getRelatedEntities();

        //fixme should be optimized
        if($id){
            $entity = $model::find($id);
        }else{
            $entity = new $model;
        }

        $fields = $entity->getConfigFields();

        $html = View::make(
            "image-storage::". $prefix .".partials.edit_form",
            compact('entity','fields', 'relatedEntities')
        )->render();

        return Response::json(array(
            'status' => true,
            'html' => $html,
        ));
    } // end getImageForm

    public function doSaveInfo()
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

    //fixme optimize searchInput
    protected function setSearchInput(){

        $model = new $this->model;
        $prefix = $model->getConfigPrefix();

        if(Input::has('image_storage_filter')){

            Session::put('image_storage_filter.'.$prefix, Input::get('image_storage_filter', array()));

        }elseif(Input::has('forget_filters')){

            Session::forget('image_storage_filter.'.$prefix);

        }
    }
}
