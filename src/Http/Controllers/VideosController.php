<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class VideosController extends AbstractImageStorageController
{
    protected $model = "Vis\\ImageStorage\\Video";

    //fixme use abstractFileController?
    public function doUploadPreviewImage()
    {
        $file = Input::file('file');
        $id   = Input::get('id');

        $video = $this->model->find($id);

        $image = new Image;

        if(!$image->setSourceFile($file)){
            return Response::json( array( 'status' => false, 'message'   => $image->getErrorMessage() ));
        }

        if(!$image->setNewFileData()) {
            return Response::json( array( 'status' => false, 'message'   => $image->getErrorMessage() ));
        }

        $video->setPreviewImage($image->id);

        $data = array(
            'status' => true,
            'src'    => $video->getPreviewImage(),
            'id'     => $image->id
        );

        $this->model->flushCache();

        return Response::json($data);
    }

    public function doRemovePreviewImage()
    {
        $id    = Input::get('id');

        $video = $this->model->find($id);

        $video->unsetPreviewImage();

        $data = array(
            'status' => true,
            'src'    => $video->getPreviewImage(),
            'id'     => $video->id
        );

        $this->model->flushCache();

        return Response::json($data);
    }

}
