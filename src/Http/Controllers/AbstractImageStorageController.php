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

    public function __construct()
    {
        $this->model = new $this->model;
    }

    public function fetchIndex()
    {
        $this->setSearchInput();

        $perPage         = $this->model->getConfigPerPage();
        $title           = $this->model->getConfigTitle();
        $prefix          = $this->model->getConfigPrefix();
        $relatedEntities = $this->model->getRelatedEntities();

        $data = $this->model->filterSearch()->orderBy('id', 'DESC')->paginate($perPage);

        $lastPage = $data->lastPage();

        if (Request::ajax()) {
            $view = "image-storage::". $prefix .".partials.content";
        } else {
            $view = "image-storage::". $prefix .".index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data)
            ->with('lastPage', $lastPage)
            ->with('relatedEntities', $relatedEntities);
    }

    public function doLoadMoreEndless()
    {
        $page = Input::get('page');

        $perPage = $this->model->getConfigPerPage();
        $prefix  = $this->model->getConfigPrefix();

        $entities = $this->model->filterSearch()->orderBy('id', 'desc')->skip($perPage * $page)->limit($perPage)->get();

        $html = '';
        foreach ($entities as $entity) {
            $html .= View::make('image-storage::'.$prefix.'.partials.single_list')->with('entity', $entity)->render();
        }
        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    }

    public function doDelete()
    {
        $id = Input::get('id');

        $entity = $this->model->find($id);

        if(!$entity->beforeDeleteAction()){

            return Response::json( array(
                'status' => false,
                'message'   => $entity->getErrorMessage()
            ));
        }

        $entity->delete();

        $entity->afterDeleteAction();

        $this->model->flushCache();

        return Response::json(array(
            'id'     => $id,
            'status' => true
        ));
    }

    public function doDeleteMultiple()
    {
        $idArray = Input::get('idArray', array());

        foreach ($idArray as $key => $id){

            $entity = $this->model->find($id);

            if(!$entity->beforeDeleteAction()){
                return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
            }

            $entity->delete();

            $entity->afterDeleteAction();
        }

        $this->model->flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function doChangeActivity()
    {
        $idArray  = Input::get('idArray', array());

        $activity = Input::get('activity', 1);

        $this->model->whereIn('id', $idArray)
                    ->update(['is_active' => $activity]);

        $this->model->flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function getForm()
    {
        $id = Input::get('id');

        $prefix          = $this->model->getConfigPrefix();
        $fields          = $this->model->getConfigFields();
        $relatedEntities = $this->model->getRelatedEntities();

        $entity  = $this->model->firstOrNew(['id' => $id]);

        $html = View::make(
            "image-storage::". $prefix .".partials.edit_form",
            compact('entity','fields', 'relatedEntities')
        )->render();

        return Response::json(array(
            'status' => true,
            'html' => $html,
        ));
    }

    public function doSaveInfo()
    {

        $fields = Input::except('relations');

        $prefix = $this->model->getConfigPrefix();

        $entity = $this->model->firstOrNew(['id' => $fields['id']]);

        $entity->setFields($fields);

        if(!$entity->beforeSaveAction()){
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        $entity->save();

        $entity->afterSaveAction();

        $html = View::make('image-storage::'. $prefix .'.partials.single_list')->with('entity', $entity)->render();

        $this->model->flushCache();

        return Response::json(array(
            'html'   => $html,
            'id'     => $entity->id,
            'status' => true,
        ));
    }

    protected function setSearchInput(){

        $prefix = $this->model->getConfigPrefix();

        if(Input::has('image_storage_filter')){

            Session::put('image_storage_filter.'.$prefix, Input::get('image_storage_filter', array()));

        }elseif(Input::has('forget_filters')){

            Session::forget('image_storage_filter.'.$prefix);

        }
    }
}
