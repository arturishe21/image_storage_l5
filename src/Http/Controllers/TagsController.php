<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class TagsController extends AbstractImageStorageController
{
    protected $model = "Vis\\ImageStorage\\Tag";

    public function doAddImagesToTags()
    {
        $model = $this->model;

        $idTags  = Input::get('idTags', array());
        $idArray = Input::get('idArray', array());

        foreach ($idTags as $key => $id){
            $entity = $model::find($id);
            $entity->relateImagesToTag($idArray);
        }

        return Response::json(array(
            'status' => true
        ));

    }

    public function doAddVideosToTags()
    {
        $model = $this->model;

        $idTags  = Input::get('idTags', array());
        $idArray = Input::get('idArray', array());

        foreach ($idTags as $key => $id){
            $entity = $model::find($id);
            $entity->relateVideosToTag($idArray);
        }

        return Response::json(array(
            'status' => true
        ));

    }

}
