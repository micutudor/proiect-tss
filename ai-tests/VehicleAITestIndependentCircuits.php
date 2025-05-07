<?php

use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleTestRewritten extends TestCase
{
    private function mockRequestWithBody(array $body): ServerRequestInterface {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        return $request;
    }

    private function createResponse(): ResponseInterface {
        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        return $responseFactory->createResponse()->withBody($streamFactory->createStream());
    }

    public function testCreateWithInvalidPlateReturnsBadRequest()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->expects($this->never())->method('save');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequestWithBody(['numar_inmatriculare' => 'WRONG123']);
        $response = $this->createResponse();

        // Act
        $result = $controller->create($request, $response);

        // Assert
        $this->assertSame(400, $result->getStatusCode());
    }

    public function testCreateWithValidPlateTriggersSaveAndReturnsCreated()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->expects($this->once())->method('save');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequestWithBody(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createResponse();

        // Act
        $result = $controller->create($request, $response);

        // Assert
        $this->assertSame(201, $result->getStatusCode());
    }

    public function testUpdateWhenVehicleNotFoundReturnsNotFound()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequestWithBody(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createResponse();

        // Act
        $result = $controller->update($request, $response, ['id' => 42]);

        // Assert
        $this->assertSame(404, $result->getStatusCode());
    }

    public function testUpdateWithInvalidPlateReturnsBadRequest()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequestWithBody(['numar_inmatriculare' => 'INVALID']);
        $response = $this->createResponse();

        // Act
        $result = $controller->update($request, $response, ['id' => 1]);

        // Assert
        $this->assertSame(400, $result->getStatusCode());
    }

    public function testUpdateWithValidPlatePerformsUpdateAndReturnsOK()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);
        $vehicle->expects($this->once())->method('update');

        $controller = new VehicleController($vehicle);
        $request = $this->mockRequestWithBody(['numar_inmatriculare' => 'AB123CD']);
        $response = $this->createResponse();

        // Act
        $result = $controller->update($request, $response, ['id' => 1]);

        // Assert
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testGetByIdWhenNotFoundReturns404()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createResponse();

        // Act
        $result = $controller->getById($request, $response, ['id' => 404]);

        // Assert
        $this->assertSame(404, $result->getStatusCode());
    }

    public function testGetByIdWhenFoundReturnsOK()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createResponse();

        // Act
        $result = $controller->getById($request, $response, ['id' => 1]);

        // Assert
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testDeleteWhenVehicleNotFoundReturns404()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(false);

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createResponse();

        // Act
        $result = $controller->delete($request, $response, ['id' => 999]);

        // Assert
        $this->assertSame(404, $result->getStatusCode());
    }

    public function testDeleteWhenVehicleFoundDeletesAndReturnsNoContent()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('find')->willReturn(['id' => 1]);
        $vehicle->expects($this->once())->method('delete');

        $controller = new VehicleController($vehicle);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createResponse();

        // Act
        $result = $controller->delete($request, $response, ['id' => 1]);

        // Assert
        $this->assertSame(204, $result->getStatusCode());
    }
}
