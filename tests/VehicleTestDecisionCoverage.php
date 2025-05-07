<?php

namespace Tests\Controllers;

use App\controllers\VehicleController;
use App\models\Vehicle;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class VehicleTestDecisionCoverage extends TestCase
{
    private $vehicle;
    private $response;
    private $stream;
    private $controller;

    protected function setUp(): void
    {
        $this->vehicle = $this->createMock(Vehicle::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('withStatus')->willReturn($this->response);
        $this->response->method('withHeader')->willReturn($this->response);

        $this->controller = new VehicleController($this->vehicle);
    }

    public function testGetAllVehicles()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('all')->willReturn([['id' => 1, 'numar_inmatriculare' => 'B682SPM']]);

        $result = $this->controller->getAll($request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> FALSE branch
    public function testGetByIdFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('find')->willReturn(['id' => 1, 'numar_inmatriculare' => 'B682SPM']);

        $result = $this->controller->getById($request, $this->response, ['id' => 1]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> TRUE branch
    public function testGetByIdNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('find')->willReturn(false);
        
        $result = $this->controller->getById($request, $this->response, ['id' => 999]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!preg_match(...)) -> FALSE branch
    public function testCreateValid()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'B111CCC']);

        $this->vehicle->expects($this->once())->method('save');

        $result = $this->controller->create($request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!preg_match(...)) -> TRUE branch
    public function testCreateInvalid()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'invalid-number']);

        $result = $this->controller->create($request, $this->response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> FALSE branch
    // covers: if (!preg_match(...)) -> FALSE branch
    public function testUpdateFoundValid()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'B111CCC']);

        $this->vehicle->method('find')->willReturn(['id' => 1]);
        $this->vehicle->expects($this->once())->method('update');

        $result = $this->controller->update($request, $this->response, ['id' => 1]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> FALSE branch
    // covers: if (!preg_match(...)) -> TRUE branch
    public function testUpdateFoundInvalid()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'invalid']);

        $this->vehicle->method('find')->willReturn(['id' => 1]);

        $result = $this->controller->update($request, $this->response, ['id' => 1]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> TRUE branch
    public function testUpdateNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('find')->willReturn(false);

        $result = $this->controller->update($request, $this->response, ['id' => 999]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> FALSE branch
    public function testDeleteFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->vehicle->method('find')->willReturn(['id' => 1]);
        $this->vehicle->expects($this->once())->method('delete')->with(1);

        $result = $this->controller->delete($request, $this->response, ['id' => 1]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    // covers: if (!$vehicle) -> TRUE branch
    public function testDeleteNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('find')->willReturn(false);

        $result = $this->controller->delete($request, $this->response, ['id' => 999]);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}