<?php
    Route::any(
        'tags', array(
            'as' => 'tags_all',
            'uses' => 'Vis\ImageStorage\TagsController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'tags/delete_tag', array(
                    'as' => 'delete_tag',
                    'uses' => 'Vis\ImageStorage\TagsController@doDelete'
                )
            );

            Route::post(
                'tags/get_edit_form', array(
                    'as' => 'get_tags_edit_form',
                    'uses' => 'Vis\ImageStorage\TagsController@getForm'
                )
            );


            Route::post(
                'tags/save_tag_info', array(
                    'as' => 'save_tag_info',
                    'uses' => 'Vis\ImageStorage\TagsController@doSaveInfo'
                )
            );

            Route::post(
                'tags/add_images_to_tags', array(
                    'as' => 'add_images_to_tags',
                    'uses' => 'Vis\ImageStorage\TagsController@doAddImagesToTags'
                )
            );
    }


