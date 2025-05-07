<?php

use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleAITestEquivalencePartitioning extends TestCase
{
    private function mockRequest(array $parsedBody = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($parsedBody);
        return $request;
    }

    private function mockResponse(): ResponseInterface
    {
        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        $response = $responseFactory->createResponse();
        $body = $streamFactory->createStream();
        return $response->withBody($body);
    }

    /** @test */
    public function shouldCreateVehicleWithValidLicensePlate()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->expects($this->once())->method('save');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequest(['numar_inmatriculare' => 'B123ABC']);
        $response = $this->mockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }

    /** @test */
    public function shouldRejectVehicleCreationWithInvalidLicensePlate()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->expects($this->never())->method('save');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequest(['numar_inmatriculare' => '123ABC']);
        $response = $this->mockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }

    /** @test */
    public function shouldRetrieveVehicleByIdIfExists()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1, 'numar_inmatriculare' => 'B123ABC']);

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->mockResponse();

        $result = $controller->getById($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function shouldReturnNotFoundWhenVehicleIdDoesNotExist()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->mockResponse();

        $result = $controller->getById($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    /** @test */
    public function shouldUpdateVehicleWithValidIdAndLicensePlate()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);
        $vehicle->expects($this->once())->method('update');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequest(['numar_inmatriculare' => 'CJ99XYZ']);
        $response = $this->mockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    /** @test */
    public function shouldRejectUpdateWithInvalidLicensePlateFormat()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);
        $vehicle->expects($this->never())->method('update');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequest(['numar_inmatriculare' => 'XYZ9999']);
        $response = $this->mockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(400, $result->getStatusCode());
    }

    /** @test */
    public function shouldReturnNotFoundWhenUpdatingNonExistentVehicle()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);
        $vehicle->expects($this->never())->method('update');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequest(['numar_inmatriculare' => 'B100XYZ']);
        $response = $this->mockResponse();

        $result = $controller->update($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    /** @test */
    public function shouldDeleteVehicleIfExists()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);
        $vehicle->expects($this->once())->method('delete');

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->mockResponse();

        $result = $controller->delete($request, $response, ['id' => 1]);
        $this->assertEquals(204, $result->getStatusCode());
    }

    /** @test */
    public function shouldReturnNotFoundWhenDeletingNonExistentVehicle()
    {
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);
        $vehicle->expects($this->never())->method('delete');

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->mockResponse();

        $result = $controller->delete($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }
}
