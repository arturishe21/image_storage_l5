<?php
return array(
    'title' => "Видео",

    'per_page' => 40,

    'api' => array(

        //enable or disable api requests
        'enabled'    => true,

        // enables caching of API results, leave 0 to disable caching
        'cache_minutes' => 60,

        // use api data to fill in title & description
        'set_data'   => true,

        'video_existence_error' => "Не удалось найти видео на {type} с идентификатором: {id}",

        'youtube' => array(

            //check if video exists on youtube. works without api_key
            'video_check_url' => 'https://www.youtube.com/oembed',

            //gets preview image of video in defined quality. works without api_key
            'preview_url' => 'https://img.youtube.com/vi/{id}/{quality}.jpg',

            //possible values: 0, 1, 2, 3, default, default, mqdefault, hqdefault, sddefault, maxresdefault
            'preview_quality' => 'maxresdefault',

            //https://developers.google.com/youtube/v3/docs/videos/list
            'api_url' => 'https://www.googleapis.com/youtube/v3/videos',

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
            'tabs' => config('translations.config.languages')
        ),
        'description' => array(
            'caption' => 'Описание',
            'type' => 'textarea',
            'field' => 'text',
            'tabs' => config('translations.config.languages')

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
