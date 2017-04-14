<?php
return array(
    'title' => "Теги",

    'per_page' => 20,

    //Only text\textarea\checkbox\datetime fields are supported for now
    'fields' => array(
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
        'is_active' => array(
            'caption' => 'Тег активен',
            'type' => 'checkbox',
            'options' => array(
                1 => 'Активные',
                0 => 'He aктивные',
            ),
            'field' => 'tinyInteger',
        ),
    ),

);
