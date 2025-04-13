<?php
require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use App\controllers\VehicleController;
use App\models\Vehicle;
use App\config\Database;

$container = new Container();

$container->set(Database::class, function() {
  return new Database();
});

$container->set(Vehicle::class, function ($container) {
  return new Vehicle($container->get(Database::class)); 
});

$container->set(VehicleController::class, function ($container) {
  return new VehicleController($container->get(Vehicle::class));
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->get('/vehicles', [VehicleController::class, 'getAll']);
$app->get('/vehicles/{id}', [VehicleController::class, 'getById']);
$app->post('/vehicles', [VehicleController::class, 'create']);
$app->put('/vehicles/{id}', [VehicleController::class, 'update']);
$app->delete('/vehicles/{id}', [VehicleController::class, 'delete']);

$app->run();