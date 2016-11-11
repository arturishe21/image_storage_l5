
В composer.json добавляем в блок require
```json
 "vis/mail_templates_l5": "1.*"
```

Выполняем
```json
composer update
```

Добавляем в app.php в массив providers
```php
  Vis\MailTemplates\MailTemplatesServiceProvider::class
```

Выполняем миграцию таблиц
```json
   php artisan migrate --path=vendor/vis/mail_templates_l5/src/Migrations
```

Публикуем js и конфиги файлы
```json
   php artisan vendor:publish --tag=mail-templates --force
```

В файле config/builder/admin.php в массив menu в настройки добавляем
```php
 	   array(
          'title' => 'Почта',
          'icon'  => 'envelope-o',
          'check' => function() {
              return true;
          },
          'submenu' => array(
              array(
                  'title' => "Шаблоны писем",
                  'link'  => '/emails/letter_template',
                  'check' => function() {
                      return true;
                  }
              ),
              array(
                  'title' => "Письма",
                  'link'  => '/emails/mailer',
                  'check' => function() {
                      return true;
                  }
              ),
          )
      ),
```

Использование
сверху
```php
    use Vis\MailTemplates\MailT;
```

вызов

```php
    $mail = new MailT("alias шаблона письма", array(
        "fio" => "Вася",
        "phone" => "097 000 00 00"
    ));
    $mail->to = "email";
    $mail->attach = Input::file('file_name'); //если нужно много файлов переслать, то оформлять как массив : array(Input::file('file_name'), Input::file('file_name'))

    $mail->send();
```
