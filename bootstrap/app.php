<?php
session_start();
use Respect\Validation\Validator as v;
use \Illuminate\Database\Capsule\Manager;
use App\Validation\Validator;
use App\Controllers\HomeController;
use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\PasswordController;
use App\Middleware\ValidationErrorsMiddleware;
use App\Middleware\OldInputMiddleware;
use App\Middleware\CsrfViewMiddleware;
use \Slim\Views\Twig;
use \Slim\Views\TwigExtension;
use \Slim\App;

require __DIR__ . '/../vendor/autoload.php';

$app = new App([
    'settings' => [
        'displayErrorDetails' => true,
        //'determineRouteBeforeAppMiddleware' => true,
        //'addContentLengthHeader' => false,
        //'db' => [
        //    'driver' => 'mysql',
        //    'host' => 'localhost',
        //    'database' => 'codecourse',
        //    'username' => 'root',
        //    'password' => '190790edu',
        //    'charset'   => 'utf8',
        //    'collation' => 'utf8_unicode_ci',
        //    'prefix'    => '',
        //]
        'db' => [
            'driver' => 'mysql',
            'host' => 'sql304.rf.gd',
            'database' => 'rfgd_18867930_galeria',
            'username' => 'rfgd_18867930',
            'password' => '190790edu',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]
    ],
]);

$container = $app->getContainer();

$capsule = new Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule){
    return $capsule;
};

$container['auth'] = function($container){
    return new \App\Auth\Auth;
};

$container['flash'] = function($container){
    return new \Slim\Flash\Messages;
};

$container['view'] = function($container){

    $view = new Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);

    $view->addExtension(new TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;

};

$container['validator'] = function($container){
    return new Validator;
};

$container['HomeController'] = function($container){
    return new HomeController($container);
};
$container['AuthController'] = function($container){
    return new AuthController($container);
};
$container['PasswordController'] = function($container){
    return new PasswordController($container);
};

$container['csrf'] = function($container){
    return new \Slim\Csrf\Guard;
};

$app->add(new ValidationErrorsMiddleware($container));
$app->add(new OldInputMiddleware($container));
$app->add(new CsrfViewMiddleware($container));
$app->add($container->csrf);

v::with('App\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
