<?php

namespace Tests\Controllers;

use App\controllers\VehicleController;
use App\models\Vehicle;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class VehicleAITestDecisionCoverage extends TestCase
{
    private Vehicle $vehicle;
    private ResponseInterface $response;
    private StreamInterface $stream;
    private VehicleController $controller;

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

    private function createRequest(array $body = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        return $request;
    }

    public function testGetAllVehicles(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $this->vehicle->method('all')->willReturn([
            ['id' => 1, 'numar_inmatriculare' => 'B123ABC']
        ]);

        $result = $this->controller->getAll($request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetByIdFound(): void
    {
        $this->vehicle->method('find')->with(1)->willReturn(['id' => 1]);

        $result = $this->controller->getById(
            $this->createMock(ServerRequestInterface::class),
            $this->response,
            ['id' => 1]
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetByIdNotFound(): void
    {
        $this->vehicle->method('find')->with(999)->willReturn(false);

        $result = $this->controller->getById(
            $this->createMock(ServerRequestInterface::class),
            $this->response,
            ['id' => 999]
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCreateValid(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'B123ABC']);

        $this->vehicle->expects($this->once())->method('save');

        $result = $this->controller->create($request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCreateInvalid(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'invalid-plate']);

        $this->vehicle->expects($this->never())->method('save');

        $result = $this->controller->create($request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testCreateMissingPlate(): void
    {
        $request = $this->createRequest([]);

        $this->vehicle->expects($this->never())->method('save');

        $result = $this->controller->create($request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testUpdateFoundValid(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'B123ABC']);

        $this->vehicle->method('find')->with(1)->willReturn(['id' => 1]);
        $this->vehicle->expects($this->once())->method('update')->with(1, ['numar_inmatriculare' => 'B123ABC']);

        $result = $this->controller->update($request, $this->response, ['id' => 1]);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testUpdateFoundInvalid(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'invalid']);

        $this->vehicle->method('find')->with(1)->willReturn(['id' => 1]);
        $this->vehicle->expects($this->never())->method('update');

        $result = $this->controller->update($request, $this->response, ['id' => 1]);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testUpdateNotFound(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'B123ABC']);

        $this->vehicle->method('find')->with(999)->willReturn(false);
        $this->vehicle->expects($this->never())->method('update');

        $result = $this->controller->update($request, $this->response, ['id' => 999]);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDeleteFound(): void
    {
        $this->vehicle->method('find')->with(1)->willReturn(['id' => 1]);
        $this->vehicle->expects($this->once())->method('delete')->with(1);

        $result = $this->controller->delete(
            $this->createMock(ServerRequestInterface::class),
            $this->response,
            ['id' => 1]
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDeleteNotFound(): void
    {
        $this->vehicle->method('find')->with(999)->willReturn(false);
        $this->vehicle->expects($this->never())->method('delete');

        $result = $this->controller->delete(
            $this->createMock(ServerRequestInterface::class),
            $this->response,
            ['id' => 999]
        );

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}
