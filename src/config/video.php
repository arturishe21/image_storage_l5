<?php
return array(
    'title' => "Видео",

    'per_page' => 40,

    'api' => array(

        //enable or disable api requests
        'enabled'    => true,

        // use api data to fill in title & description
        'set_data'   => true,

        'youtube' => array(
            //check if video exists on youtube. works without api_key
            'video_existence_validation' => array(
                'check_url' => 'https://www.youtube.com/oembed?format=json&url=http://www.youtube.com/watch?v=[id_youtube]',
                'error_message' => "Не удалось найти видео на YouTube с идентификатором: [id_youtube]"
            ),

            'preview_url' => 'https://img.youtube.com/vi/{id}/{quality}.jpg',

            //possible values: 1, 2, 3, default, hqdefault, maxresdefault
            'preview_quality' => 'maxresdefault',

            //https://developers.google.com/youtube/v3/docs/videos/list
            'api_url' => 'https://www.googleapis.com/youtube/v3/videos?id={id}&part={part}&key={key}',

            'api_part' => 'snippet,statistics',

            //https://developers.google.com/youtube/v3/getting-started
            'api_key' => 'AIzaSyDWPTTGEANYwXwAk8QMg9bQTzzBatmhxbc',

        ),
    ),

    //Only text\textarea\checkbox\datetime fields are supported for now
    'fields' => array(
        'id_youtube' => array(
            'caption' => 'Идентификатор видео',
            'type' => 'text',
            'field' => 'string',
            'placeholder' => 'Идентификатор видео',
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
