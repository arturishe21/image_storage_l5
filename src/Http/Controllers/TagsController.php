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

    public function doRelateToTags($type)
    {
        $idTags  = Input::get('idTags', array());
        $idArray = Input::get('idArray', array());

        foreach ($idTags as $key => $id){

            $tag = $this->model->find($id);

            $tag->relateToTag($idArray,$type);
        }

        return Response::json(array(
            'status' => true
        ));

    }

}
