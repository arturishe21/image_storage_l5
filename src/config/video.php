<?php
return array(
        'title' => "Видео",

        'per_page' => 40,

        'youtube' => array(

            'video_existance_validation' => array(
                'enabled' => true,
                'check_url' => 'https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=[id_youtube]',
                'error_message' => "Не удалось найти видео на YouTube с идентификатором: [id_youtube]"
            ),

            'preview_url' => 'https://img.youtube.com/vi/{id}/{quality}.jpg',

            //possible values: 1, 2, 3, default, hqdefault, maxresdefault
            'preview_quality' => 'maxresdefault',

            'use_api' => true,

            //https://developers.google.com/youtube/v3/docs/videos/list
            'api_url' => 'https://www.googleapis.com/youtube/v3/videos?id={id}&part={part}&key={key}',

            'api_part'=> 'snippet,statistics',

            //https://developers.google.com/youtube/v3/getting-started
            'api_key' => 'AIzaSyDWPTTGEANYwXwAk8QMg9bQTzzBatmhxbc',

            /* store youtube data in db */
            'store_data' => true,

            /* use youtube data to fill in title&description */
            'set_data' => true,
        ),

        //Only text\textarea\checkbox\datetime fields are supported for now
        'fields' => array(
            'id_youtube' => array(
                'caption' => 'Идентификатор на Youtube',
                'type' => 'text',
                'field' => 'string',
                'placeholder' => 'Идентификатор на Youtube',
            ),
            'title' => array(
                'caption' => 'Название',
                'type' => 'text',
                'field' => 'string',
                'tabs' => array(
                    array(
                        'caption' => 'ru',
                        'postfix' => '',
                        'placeholder' => 'Название русском'
                    ),
                    array(
                        'caption' => 'ua',
                        'postfix' => '_ua',
                        'placeholder' => 'Название на украинском'
                    ),
                )
            ),
            'description' => array(
                'caption' => 'Описание',
                'type' => 'textarea',
                'field' => 'text',
                'tabs' => array(
                    array(
                        'caption' => 'ru',
                        'postfix' => '',
                        'placeholder' => 'Описание на русском'
                    ),
                    array(
                        'caption' => 'ua',
                        'postfix' => '_ua',
                        'placeholder' => 'Описание на украинском'
                    ),
                )
            ),
            'is_active' => array(
                'caption' => 'Видео активно',
                'type' => 'checkbox',
                'options' => array(
                    1 => 'Активные',
                    0 => 'He aктивные',
                ),
                'field' => 'tinyInteger',
            ),
        ),


);