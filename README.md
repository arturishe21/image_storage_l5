
В composer.json добавляем в блок require
```json
 "vis/image_storage_l5": "1.*"
```

Выполняем
```json
composer update
```

Добавляем в app.php в массив providers
```php
    Vis\ImageStorage\ImageStorageServiceProvider::class,
```

Выполняем миграцию таблиц
```json
   php artisan migrate --path=vendor/vis/image-storage/src/Migrations
```

Публикуем js и конфиги файлы
```json
   php artisan vendor:publish --tag=image-storage --force
```

В файле config/builder/admin.php в массив menu в настройки добавляем
```php
        array(
            'title' => 'Фотохранилище',
            'icon'  => 'picture-o',
            'check' => function() {
                return true;
            },
            'submenu' => array(
                array(
                    'title' => "Изображения",
                    'link'  => '/image_storage/images',
                    'check' => function() {
                        return true;
                    }
                ),
                array(
                    'title' => "Галереи",
                    'link'  => '/image_storage/galleries',
                    'check' => function() {
                        return true;
                    }
                ),
/*                array(
                    'title' => "Теги",
                    'link'  => '/image_storage/tags',
                    'check' => function() {
                        return true;
                    }
                ),*/
            )
        ),
```

Использование
сверху
```php
    use Vis\ImageStorage\Gallery;
```

вызов

```php
    ....
```
