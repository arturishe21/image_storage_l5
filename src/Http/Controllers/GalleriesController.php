<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class GalleriesController extends AbstractImageStorageController
{
    protected $model = "Vis\\ImageStorage\\Gallery";

    public function doChangeGalleryImagesOrder()
    {
        $model = new $this->model;

        $id = Input::get('idGallery');

        $entity = $model::find($id);

        $images = Input::get('images', array());

        $entity->changeGalleryImageOrder($images);

        return Response::json(array(
            'status' => true
        ));
    } // end doChangeGalleryImagesPriority

    public function doDeleteImageGalleryRelation()
    {
        $model = new $this->model;

        $id    = Input::get('idGallery');
        $image = Input::get('idImage');

        $entity = $model::find($id);

        $entity->deleteImageGalleryRelation($image);

        return Response::json(array(
            'status' => true
        ));
    }

    public function doCreateGalleryWithImages()
    {
        $entity = new $this->model;

        $galleryName = Input::get('galleryName');
        $images      = Input::get('images', array());

        $entity->title = $galleryName;
        $entity->save();

        $entity->relateImagesToGallery($images);

        return Response::json(array(
            'status' => true
        ));

    }

    public function doAddImagesToGalleries()
    {
        $model = $this->model;

        $galleries = Input::get('galleries', array());
        $images = Input::get('images', array());

        foreach ($galleries as $key => $id){
            $entity = $model::find($id);
            $entity->relateImagesToGallery($images);
        }

        return Response::json(array(
            'status' => true
        ));

    }

    public function doSetGalleryImagePreview()
    {
        $model = new $this->model;

        $id    = Input::get('idGallery');
        $image = Input::get('idImage');

        $entity = $model::find($id);

        $entity->setPreviewImage($image);

        return Response::json(array(
            'status' => true
        ));

    }

}
