<?php namespace Vis\ImageStorage;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;


class ImagesController extends Controller
{
    protected $model = "Vis\\ImageStorage\\Image";

    public function fetchIndex()
    {
        $model = new $this->model;

        $perPage = $model->getConfigPerPage();
        $title = $model->getConfigTitle();

        $data = $model::filterSearch()->orderBy('id', 'DESC')->limit($perPage)->get();

        $galleries = Gallery::active()->get();
        $tags = Tag::active()->get();

        if (Request::ajax()) {
            $view = "image-storage::images.partials.content";
        } else {
            $view = "image-storage::images.index";
        }

        return View::make($view)
            ->with('title', $title)
            ->with('data', $data)
            ->with('galleries', $galleries)
            ->with('tags', $tags);
    }

    public function doSearchImages()
    {
        $model = new $this->model;
        $perPage = $model->getConfigPerPage();

        $images = $model::filterSearch()
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get();

        $html = '';
        foreach ($images as $image) {
            $html .= View::make('image-storage::images.partials.list_image')->with('image', $image)->render();
        }

        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    }

    public function doLoadMoreImages()
    {
        $model = new $this->model;
        $page = Input::get('page');

        $perPage = $model->getConfigPerPage();
        $images = $model::filterSearch()->orderBy('id', 'desc')->skip($perPage * $page)->limit($perPage)->get();
        $html = '';
        foreach ($images as $image) {
            $html .= View::make('image-storage::images.partials.list_image')->with('image', $image)->render();
        }
        return Response::json(array(
            'status' => true,
            'html'   => $html
        ));
    } // end doLoadMoreImages

    public function doUploadImage()
    {
        $model = $this->model;

        $file = Input::file('image');

        $entity = new $model;

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getUploadErrorMessage() ));
        }

        $entity->setImageData();
        $entity->setImageTitle();
        $entity->save();

        $entity->doMakeSourceFile();
        $entity->doImageVariations();
        $entity->save();

        $html = View::make('image-storage::images.partials.list_image')->with('image', $entity)->render();

        $model::flushCache();

        $data = array(
            'status' => true,
            'html'   => $html,
            'id'     => $entity->id
        );

        return Response::json($data);
    } // end doUploadImage

    public function doReplaceSingleImage()
    {
        $model = new $this->model;

        $file = Input::file('image');
        $size = Input::get('type');
        $id   = Input::get('id');

        $entity = $model::find($id);

        if(!$entity->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $entity->getUploadErrorMessage() ));
        }

        $entity->replaceSingleImage($size);

        $entity->save();

        $entity::flushCache();

        $data = array(
            'status' => true,
            'src'    => asset($entity->getSource($size)),
        );

        return Response::json($data);

    }

    public function getImageForm()
    {
        $model = new $this->model;
        $id   = Input::get('id');

        $entity = $model::find($id);

        $sizes = $model->getConfigSizes();

        $fields = $model->getConfigFields();

        $galleries = Gallery::active()->get();
        $tags = Tag::active()->get();

        //fixme переписать под модель
        $relatedGalleries = \DB::table('vis_images2galleries')->where('id_image', $entity->id)->lists('id_gallery') ? : array();
        $relatedTags = \DB::table('vis_images2tags')->where('id_image', $entity->id)->lists('id_tag') ? : array();
        
        $html = View::make(
            'image-storage::images.partials.image_form',
            compact(
                'entity',
                'sizes',
                'galleries',
                'tags',
                'relatedGalleries',
                'relatedTags',
                'fields'
            )
        )->render();

        return Response::json(array(
            'status' => true,
            'html'   => $html,
        ));
    } // end getImageForm

    public function doDeleteImage()
    {
        $id    = Input::get('id');
        $model = new $this->model;

        $image = $model::find($id);

        $image->doDeleteImageFiles();

        $image->delete();

        $model::flushCache();

        return Response::json(array(
            'status' => true
        ));
    }

    public function doSaveImageInfo()
    {
        $model = new $this->model;
        $fields = Input::except('relations');

        $image = $model::find($fields['id']);

        $image->makeImageRelations();

        $image->setFields($fields);

        $image->save();

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    } // end doSaveImageInfo

    public function doOptimizeImage()
    {

        $model = new $this->model;
        $id   = Input::get('id');
        $size = Input::get('type');

        $image = $model::find($id);

        $image->optimizeImage($size);

        $model::flushCache();

        return Response::json(array(
            'status' => true,
        ));
    }


}
