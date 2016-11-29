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

        $model = new $this->model;
        $perPage = $model->getConfigPerPage();
        $prefix = $model->getConfigPrefix();

        $entities = $model::filterSearch()->orderBy('id', 'desc')->skip($perPage * $page)->limit($perPage)->get();
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
        $id    = Input::get('id');
        $model = new $this->model;

        $entity = $model::find($id);

        if(!$entity->beforeDeleteAction()){
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        $entity->delete();

        $entity->afterDeleteAction();

        $model::flushCache();

        return Response::json(array(
            'id'     => $id,
            'status' => true
        ));
    }

    public function getForm()
    {
        $id = Input::get('id');

        $model = new $this->model;
        $prefix = $model->getConfigPrefix();
        $relatedEntities = $model->getRelatedEntities();

        $entity = $model::firstOrNew(['id' => $id]);

        $fields = $entity->getConfigFields();

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
        $model = new $this->model;

        $fields = Input::except('relations');

        $entity = $model::firstOrNew(['id' => $fields['id']]);

        $prefix = $entity->getConfigPrefix();

        $entity->setFields($fields);

        if(!$entity->beforeSaveAction()){
            return Response::json( array( 'status' => false, 'message'   => $entity->getErrorMessage() ));
        }

        $entity->save();

        $entity->afterSaveAction();

        $html = View::make('image-storage::'. $prefix .'.partials.single_list')->with('entity', $entity)->render();

        $model::flushCache();

        return Response::json(array(
            'html'   => $html,
            'id'     => $entity->id,
            'status' => true,
        ));
    }

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
