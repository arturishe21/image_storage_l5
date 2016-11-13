<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class GalleriesController extends Controller
{
    protected $model = "Vis\\ImageStorage\\Gallery";

    public function fetchIndex()
    {
        $model = new $this->model;

        $perPage = $model->getConfigPerPage();
        $title = $model->getConfigTitle();

        $data = $model::filterSearch()->orderBy('id', 'DESC')->with('tags')->paginate($perPage);

        $galleries = Gallery::active()->get();
        $tags = Tag::active()->get();

        if (Request::ajax()) {
            $view = "image-storage::galleries.partials.content";
        } else {
            $view = "image-storage::galleries.index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data)
            ->with('tags', $tags)
            ->with('galleries', $galleries);

    }

    public function doSearchGalleries()
    {
        $html = $this->fetchIndex()->render();

         return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    }

    public function doDeleteGallery()
    {
        $model = new $this->model;


        $model::destroy(Input::get('id'));

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    }

    public function getGalleryForm()
    {
        $model = new $this->model;

        $entity = $model::find(Input::get('id'));

        $fields = $model->getConfigFields();

        $tags = Tag::active()->get();

        //fixme переписать под модель
        $relatedTags = \DB::table('vis_images2galleries')->where('id_gallery', $entity->id)->lists('id_image') ? : array();

        $html = View::make(
            'image-storage::galleries.partials.gallery_form',
            compact(
                'entity',
                'tags',
                'relatedTags',
                'fields'
            )
        )->render();

        return Response::json(array(
            'status' => true,
            'html'   => $html,
        ));
    } // end getImageForm


    public function doSaveGalleryInfo()
    {
        $model = new $this->model;

        $fields = Input::except('relations');

        $image = $model::find($fields['id']);

        $image->setFields($fields);

        $image->save();

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    } // end doSaveImageInfo

    public function doChangeGalleryImagesOrder()
    {
        $model = new $this->model;

        $entity = $model::find(Input::get('idGallery'));

        $images = Input::get('images', array());

        $entity->changeGalleryImageOrder($images);

        return Response::json(array(
            'status' => true
        ));
    } // end doChangeGalleryImagesPriority

    public function doDeleteImageGalleryRelation()
    {
        $model = new $this->model;

        $entity = $model::find(Input::get('idGallery'));

        $image = Input::get('idImage');

        $entity->deleteImageGalleryRelation($image);

        return Response::json(array(
            'status' => true
        ));
    }



}
