<?php
use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleTestIndependentCircuits extends TestCase
{
    private function createMockRequest(array $parsedBody = []): ServerRequestInterface {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($parsedBody);
        return $request;
    }

    private function createMockResponse(): ResponseInterface {
        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        $response = $responseFactory->createResponse();
        $body = $streamFactory->createStream();
        return $response->withBody($body);
    }

    // Test Path: create -> invalid license plate -> 400
    public function testCreate_InvalidLicensePlate_Triggers400()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->never())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => '123INVALID']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Test Path: create -> valid license plate -> save -> 201
    public function testCreate_ValidLicensePlate_TriggersSaveAnd201()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }

    // Test Path: update -> not found by ID -> 404
    public function testUpdate_NotFound_Triggers404()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 99]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Test Path: update -> found -> invalid plate -> 400
    public function testUpdate_FoundButInvalidLicense_Triggers400()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'BAD123']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Test Path: update -> found -> valid plate -> update
    public function testUpdate_FoundAndValidLicense_TriggersUpdate()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->once())->method('update');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'AB123CD']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    // Test Path: getById -> not found -> 404
    public function testGetById_NotFound_Triggers404()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 1000]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Test Path: getById -> found -> return 200
    public function testGetById_Found_Returns200()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    // Test Path: delete -> not found -> 404
    public function testDelete_NotFound_Triggers404()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->delete($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Test Path: delete -> found -> delete -> 204
    public function testDelete_Found_Triggers204()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->once())->method('delete');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->delete($request, $response, ['id' => 1]);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
