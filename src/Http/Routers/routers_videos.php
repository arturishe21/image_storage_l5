<?php
    Route::any(
        'videos', array(
            'as' => 'videos_all',
            'uses' => 'Vis\ImageStorage\VideosController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'videos/delete', array(
                    'as' => 'delete_video',
                    'uses' => 'Vis\ImageStorage\VideosController@doDelete'
                )
            );

            Route::post(
                'videos/get_form', array(
                    'as' => 'get_video_form',
                    'uses' => 'Vis\ImageStorage\VideosController@getForm'
                )
            );

            Route::post(
                'videos/save_info', array(
                    'as' => 'save_video_info',
                    'uses' => 'Vis\ImageStorage\VideosController@doSaveInfo'
                )
            );

            Route::post(
                'videos/load_more', array(
                    'as' => 'load_more_videos',
                    'uses' => 'Vis\ImageStorage\VideosController@doLoadMoreEndless'
                )
            );

            Route::post(
                'videos/upload_video_preview', array(
                    'as' => 'upload_video_preview',
                    'uses' => 'Vis\ImageStorage\VideosController@doUploadPreviewImage'
                )
            );

            Route::post(
                'videos/remove_video_preview', array(
                    'as' => 'remove_video_preview',
                    'uses' => 'Vis\ImageStorage\VideosController@doRemovePreviewImage'
                )
            );
    }
