<?php
    Route::any(
        'galleries', array(
            'as' => 'galleries_all',
            'uses' => 'Vis\ImageStorage\GalleriesController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'galleries/search_galleries', array(
                    'as' => 'galleries_search',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doSearchGalleries'
                )
            );

            Route::post(
                'galleries/delete_gallery', array(
                    'as' => 'delete_image',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doDeleteGallery'
                )
            );

            Route::post(
                'galleries/get_gallery_form', array(
                    'as' => 'get_gallery_form',
                    'uses' => 'Vis\ImageStorage\GalleriesController@getGalleryForm'
                )
            );

            Route::post(
                'galleries/save_gallery_info', array(
                    'as' => 'save_gallery_info',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doSaveGalleryInfo'
                )
            );

            Route::post(
                'galleries/change_image_order', array(
                    'as' => 'change_image_order',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doChangeGalleryImagesOrder'
                )
            );

            Route::post(
                'galleries/delete_image_relation', array(
                    'as' => 'delete_image_relation',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doDeleteImageGalleryRelation'
                )
            );

            Route::post(
                'galleries/create_gallery_with_images', array(
                    'as' => 'create_gallery_with_images',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doCreateGalleryWithImages'
                )
            );

            Route::post(
                'galleries/add_images_to_galleries', array(
                    'as' => 'add_images_to_galleries',
                    'uses' => 'Vis\ImageStorage\GalleriesController@doAddImagesToGalleries'
                )
            );
    }


