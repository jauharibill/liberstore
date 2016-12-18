# Laravel Session middleware for SlimPHP

This middleware allows you to use Laravel Session library with Slim 3.
The benefit of this is being able to use different Session stores with the same API.

## How to use

```php
$app = new Slim\App();
$app->getContainer()->register(new Ackee\LaravelSession\PimpleSessionServiceProvider($app));
$app->add(new Ackee\LaravelSession\Middleware($app['session']));

$app->get('/', function ($req, $res, $args) {
    $this['session']->set('test', 'This is my session data');
    return $res;
});

$app->get('/test', function ($req, $res, $args) {
    $test = $this['session']->get('test');
    return $res->write($test);
});

$app->run();
