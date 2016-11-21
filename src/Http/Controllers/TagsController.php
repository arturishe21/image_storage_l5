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

}
