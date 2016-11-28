<?php
return array(

        'title' => "Галереи",

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
            'event_date' => array(
                'caption' => 'Дата события',
                'type' => 'datetime',
                'is_sorting' => true,
                'months' => 2,
                'field' => 'timestamp',
            ),
            'is_active' => array(
                'caption' => 'Галерея активна',
                'type' => 'checkbox',
                'options' => array(
                    1 => 'Активные',
                    0 => 'He aктивные',
                ),
                'field' => 'tinyInteger',
            ),
        ),

);