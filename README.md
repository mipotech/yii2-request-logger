Yii2 Request and Response Logger
================================
A useful class for generating a thorough log of all requests and responses. Especially suitable for REST APIs built upon the Yii2 framework.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mipotech/yii2-response-logger "*"
```

or add

```
"mipotech/yii2-response-logger": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \mipotech\responselogger\AutoloadExample::widget(); ?>```

./yii migrate --migrationPath=@vendor/mipotech/yii2-request-logger/migrations