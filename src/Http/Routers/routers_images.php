<?php
    Route::any(
        'images', array(
            'as' => 'images_all',
            'uses' => 'Vis\ImageStorage\ImagesController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'images/delete_image', array(
                    'as' => 'delete_image',
                    'uses' => 'Vis\ImageStorage\ImagesController@doDelete'
                )
            );

            Route::post(
                'images/get_form', array(
                    'as' => 'get_image_form',
                    'uses' => 'Vis\ImageStorage\ImagesController@getForm'
                )
            );

            Route::post(
                'images/save_image_info', array(
                    'as' => 'save_image_info',
                    'uses' => 'Vis\ImageStorage\ImagesController@doSaveInfo'
                )
            );


            Route::post(
                'images/upload', array(
                    'as' => 'upload_image',
                    'uses' => 'Vis\ImageStorage\ImagesController@doUploadImage'
                )
            );

            Route::post(
                'images/load_more', array(
                    'as' => 'load_more_images',
                    'uses' => 'Vis\ImageStorage\ImagesController@doLoadMoreImages'
                )
            );

            Route::post(
                'images/replace_single_image', array(
                    'as' => 'replace_single_image',
                    'uses' => 'Vis\ImageStorage\ImagesController@doReplaceSingleImage'
                )
            );

            Route::post(
                'images/optimize_image', array(
                    'as' => 'optimize_image',
                    'uses' => 'Vis\ImageStorage\ImagesController@doOptimizeImage'
                )
            );
    }
