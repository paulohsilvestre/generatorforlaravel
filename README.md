# generatorforlaravel

## Quick Start

#### Install Through Composer

You can either add the package directly by firing this command

``` bash
$ composer require paulohsilvestre/generatorforlaravel:~1.0
```
    
Or add in the `require` key of `composer.json` file manually

``` json
"paulohsilvestre/generatorforlaravel": "~1.0"
```

And Run the Composer update command

``` bash
$ composer update
```

#### Add Service Provider

``` php
$app->register(Generatorforlaravel\Generation\GenerationServiceProvider::class);
```
Artisan Service Provider is an optional provider required only if you want `vendor:publish` command working.

And you're done! You can now start installing any Laravel Package out there.


