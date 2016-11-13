<?php
    Route::any(
        'tags', array(
            'as' => 'tags_all',
            'uses' => 'Vis\ImageStorage\TagsController@fetchIndex'
        )
    );

    if (Request::ajax()) {
        Route::post(
            'tags/add_images_to_tags', array(
                'as' => 'add_images_to_tags',
                'uses' => 'Vis\ImageStorage\TagsController@doAddImagesToTags'
            )
        );
    }


