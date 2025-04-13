<?php
require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\controllers\VehicleController;

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/vehicles', [new VehicleController(), 'getAll']);
$app->get('/vehicles/{id}', [new VehicleController(), 'getById']);
$app->post('/vehicles', [new VehicleController(), 'create']);
$app->put('/vehicles/{id}', [new VehicleController(), 'update']);
$app->delete('/vehicles/{id}', [new VehicleController(), 'delete']);

$app->run();
