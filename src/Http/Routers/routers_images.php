<?php
    Route::any(
        'images', array(
            'as' => 'images_all',
            'uses' => 'ImagesController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'images/delete', array(
                    'as' => 'delete_image',
                    'uses' => 'ImagesController@doDelete'
                )
            );

            Route::post(
                'images/delete_multiple', array(
                    'as' => 'delete_multiple_images',
                    'uses' => 'ImagesController@doDeleteMultiple'
                )
            );

            Route::post(
                'images/get_form', array(
                    'as' => 'get_image_form',
                    'uses' => 'ImagesController@getForm'
                )
            );

            Route::post(
                'images/save_info', array(
                    'as' => 'save_image_info',
                    'uses' => 'ImagesController@doSaveInfo'
                )
            );

            Route::post(
                'images/load_more', array(
                    'as' => 'load_more_images',
                    'uses' => 'ImagesController@doLoadMoreEndless'
                )
            );

            Route::post(
                'images/upload', array(
                    'as' => 'upload_image',
                    'uses' => 'ImagesController@doUploadImage'
                )
            );

            Route::post(
                'images/replace_single_image', array(
                    'as' => 'replace_single_image',
                    'uses' => 'ImagesController@doReplaceSingleImage'
                )
            );

            Route::post(
                'images/optimize_image', array(
                    'as' => 'optimize_image',
                    'uses' => 'ImagesController@doOptimizeImage'
                )
            );
    }
