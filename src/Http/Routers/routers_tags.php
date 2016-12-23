<?php
    Route::any(
        'tags', array(
            'as' => 'tags_all',
            'uses' => 'TagsController@fetchIndex'
        )
    );

    if (Request::ajax()) {

            Route::post(
                'tags/delete', array(
                    'as' => 'delete_tag',
                    'uses' => 'TagsController@doDelete'
                )
            );

            Route::post(
                'tags/get_form', array(
                    'as' => 'get_tags_edit_form',
                    'uses' => 'TagsController@getForm'
                )
            );


            Route::post(
                'tags/save_info', array(
                    'as' => 'save_tag_info',
                    'uses' => 'TagsController@doSaveInfo'
                )
            );


            Route::post(
                'tags/add_images_to_tags', array(
                    'as' => 'add_images_to_tags',
                    'uses' => 'TagsController@doAddImagesToTags'
                )
            );

        Route::post(
            'tags/add_videos_to_tags', array(
                'as' => 'add_images_to_tags',
                'uses' => 'TagsController@doAddVideosToTags'
            )
        );
    }


