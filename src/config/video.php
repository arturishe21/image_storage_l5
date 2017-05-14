<?php
return array(
    'title' => "Видео",

    'per_page' => 40,

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
