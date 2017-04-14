<?php namespace Vis\ImageStorage;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class AbstractImageStorageFileController extends AbstractImageStorageController
{
    public function doUpload()
    {
        $file = Input::file('file');

        $prefix = $this->model->getConfigPrefix();

        $entity = $this->model;

        if (!$entity->setSourceFile($file)) {
            return Response::json(array('status' => false, 'message' => $entity->getErrorMessage()));
        }

        if (!$entity->setNewFileData()) {
            return Response::json(array('status' => false, 'message' => $entity->getErrorMessage()));
        }

        $html = View::make('image-storage::' . $prefix . '.partials.single_list')->with('entity', $entity)->render();

        $data = array(
            'status' => true,
            'html'   => $html,
            'id'     => $entity->id
        );

        $this->model->flushCache();

        return Response::json($data);
    }

    public function doReplaceSingle()
    {
        $file = Input::file('file');
        $size = Input::get('size');
        $id   = Input::get('id');

        $prefix = $this->model->getConfigPrefix();

        $entity = $this->model->find($id);

        if (!$entity->setSourceFile($file)) {
            return Response::json(array('status' => false, 'message' => $entity->getErrorMessage()));
        }

        $entity->replaceSingleFile($size);

        $entity->save();

        $this->model->flushCache();

        $html = View::make('image-storage::'. $prefix .'.partials.tab_content')
            ->with('entity', $entity)
            ->with('ident',$size)
            ->render();

        $data = array(
            'status' => true,
            'html'    => $html,
        );

        return Response::json($data);

    }
}
