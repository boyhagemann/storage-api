<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

// $app->withFacades();

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton('pdo', function() {

    // Create a PDO connection
    $connection = sprintf('mysql:host=%s;dbname=%s;charset=utf8', $_ENV['MYSQL_HOST'], $_ENV['MYSQL_DATABASE']);
    $pdo = new PDO($connection, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    return $pdo;
});
$app->singleton(\Boyhagemann\Storage\Contracts\FieldRepository::class, function() use ($app) {
    return new \Boyhagemann\Storage\Drivers\MysqlField(
        $app->make('pdo'),
        new \Boyhagemann\Storage\Validators\FieldValidator());
});
$app->singleton(\Boyhagemann\Storage\Contracts\EntityRepository::class, function() use ($app) {
    return new \Boyhagemann\Storage\Drivers\MysqlEntity(
        $app->make('pdo'),
        new \Boyhagemann\Storage\Validators\EntityValidator(),
        $app->make(\Boyhagemann\Storage\Contracts\FieldRepository::class));
});
$app->singleton(\Boyhagemann\Storage\Contracts\RecordRepository::class, function() use ($app) {
    $record = new \Boyhagemann\Storage\Drivers\MysqlRecord($app->make('pdo'));

    $record->buildValidator(function(\Boyhagemann\Storage\Contracts\Entity $entity) {
        return new \Boyhagemann\Storage\Validators\RecordValidator($entity);
    });

    return $record;
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

 $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
     palanik\lumen\Middleware\LumenCors::class
 ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);
//$app->register(\mmghv\LumenRouteBinding\RouteBindingServiceProvider::class);


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../routes/web.php';
});

return $app;
