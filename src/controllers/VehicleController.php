<?php
namespace App\controllers;

use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VehicleController {

    public function getAll(Request $request, Response $response): Response {
        $vehicles = Vehicle::all();
        $response->getBody()->write(json_encode($vehicles));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getById(Request $request, Response $response, $args): Response {
        $vehicle = Vehicle::find($args['id']);
        if (!$vehicle) {
            $response->getBody()->write(json_encode(['error' => 'Nu am gasit autovehiculul!']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode($vehicle));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response {
        $data = $request->getParsedBody();

        if (!preg_match('/^[A-Z]{1,2}\d{2,3}[A-Z]{1,3}$/', $data['numar_inmatriculare'])) {
            $response->getBody()->write(json_encode(['error' => 'Format invalid pentru numar de inmatriculare']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        Vehicle::save($data);

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, $args): Response {
        $vehicle = Vehicle::find($args['id']);

        if (!$vehicle) {
            $response->getBody()->write(json_encode(['error' => 'Nu am gasit autovehiculul!']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $data = $request->getParsedBody();

        if (!preg_match('/^[A-Z]{1,2}\d{2,3}[A-Z]{1,3}$/', $data['numar_inmatriculare'])) {
            $response->getBody()->write(json_encode(['error' => 'Format invalid pentru numar de inmatriculare']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        Vehicle::update($args['id'], $data);

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, $args): Response {
        $vehicle = Vehicle::find($args['id']);
        if (!$vehicle) {
            $response->getBody()->write(json_encode(['error' => 'Nu am gasit autovehiculul!']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        Vehicle::delete($args['id']);
        return $response->withStatus(204); // No Content
    }
}
