<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

abstract class AbstractImageStorageGalleryController extends AbstractImageStorageController
{
    public function doChangeGalleryOrder()
    {
        $model = new $this->model;

        $idGallery = Input::get('idGallery');

        $entity = $model::find($idGallery);

        $idArray = Input::get('idArray', array());

        $entity->changeGalleryOrder($idArray);

        return Response::json(array(
            'status' => true
        ));
    } // end doChangeGalleryImagesPriority

    public function doDeleteToGalleryRelation()
    {
        $model = new $this->model;

        $idGallery = Input::get('idGallery');
        $idRelated = Input::get('id');

        $entity = $model::find($idGallery);

        $entity->deleteToGalleryRelation($idRelated);

        return Response::json(array(
            'status' => true
        ));
    }

    public function doSetGalleryPreview()
    {
        $model = new $this->model;

        $idGallery = Input::get('idGallery');
        $idPreview = Input::get('idPreview');

        $entity = $model::find($idGallery);

        $entity->setPreview($idPreview);

        return Response::json(array(
            'status' => true
        ));

    }

    public function doCreateGalleryWith()
    {
        $entity = new $this->model;

        $galleryName = Input::get('galleryName');
        $idArray     = Input::get('idArray', array());

        $entity->title = $galleryName;
        $entity->save();

        $entity->relateToGallery($idArray);

        return Response::json(array(
            'status' => true
        ));
    }

    public function doAddArrayToGalleries()
    {
        $model = $this->model;

        $idGalleries = Input::get('idGalleries', array());
        $idArray     = Input::get('idArray', array());

        foreach ($idGalleries as $key => $id){
            $entity = $model::find($id);
            $entity->relateToGallery($idArray);
        }

        return Response::json(array(
            'status' => true
        ));
    }


}