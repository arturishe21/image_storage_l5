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

        Route::post(
            'tags/delete_tag', array(
                'as' => 'delete_tag',
                'uses' => 'Vis\ImageStorage\TagsController@doDeleteTag'
            )
        );

        Route::post(
            'tags/get_edit_form', array(
                'as' => 'get_tags_edit_form',
                'uses' => 'Vis\ImageStorage\TagsController@getTagForm'
            )
        );


        Route::post(
            'tags/save_tag_info', array(
                'as' => 'save_tag_info',
                'uses' => 'Vis\ImageStorage\TagsController@doSaveTagInfo'
            )
        );
    }


