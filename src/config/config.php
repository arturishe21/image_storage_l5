<?php
return array(


    'tag' => array(
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

    ),

    'gallery' => array(

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
    ),

    'image' => array(

        'title' => "Изображения",

        'per_page' => 40,

        'image_size_validation' => array(
            'enabled' => true,
            'max_image_size' => '1500000',
            'error_message' => "Превышен максимальный размер файла в [size] MB"
        ),

        'image_extension_validation' => array(
            'enabled' => true,
            'allowed_image_extensions' => array('png', 'jpg', 'jpeg'),
            'error_message' => "Допустимы только изображения форматов: [extension_list]"
        ),

        /* Quality is only applied if you're encoding JPG format since PNG compression. Value range is 0-100.*/
        'quality' => 85,

        /* Optimization with Vis\Builder\OptimizationImg. May greatly increase execution time when used to large sized photos. */
        'optimization' => true,

        /* use source file name as title when uploading images */
        'source_title' => true,

        /* store EXIF metadata in database */
        'store_exif' => true,

        /* delete files upon deleting entry from database */
        'delete_files' => true,

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
        ),


        'sizes' => array(
            'source' => array(
                'caption' => 'Оригинал',
                'default_tab' => true,
            ),

            'cms_preview' => array(
                'caption' => 'Превью в ЦМС',
                'default_tab' => false,
                'modify' => array(
                    'fit' => array(160, 160, function ($constraint) {
                        $constraint->upsize();
                    }),
                ),
            ),

            'extra_small' => array(
                'caption' => 'Очень маленькая',
                'default_tab' => false,
                'modify' => array(
                    'resize' => array(160, null, function ($constraint) {
                        $constraint->aspectRatio();
                    }),
                    'resizeCanvas' => array(160, 80, 'center', false, 'rgba(0, 0, 0, 0)'),
                ),
            ),

        ),
    ),

);