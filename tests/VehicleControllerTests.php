<?php
namespace App\tests;

use App\controllers\VehicleController;
use App\models\Vehicle;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use DI\Container;
use PHPUnit\Framework\MockObject\MockObject;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;

class VehicleControllerTests extends TestCase {

    private MockObject $vehicleMock;
    private VehicleController $controller;
    private MockObject $requestMock;
    private Response $responseMock;

    protected function setUp(): void {
        $container = new Container();

        $this->vehicleMock = $this->createMock(Vehicle::class);
        $container->set(Vehicle::class, function() {
            return $this->vehicleMock;
        });

        $this->controller = new VehicleController($this->vehicleMock);

        $this->requestMock = $this->createMock(Request::class);

        $this->responseMock = new Response();
    }

    public function testGetAllReturnsVehicles(): void {
        $vehicles = [['id' => 1, 'numar_inmatriculare' => 'B123XYZ']];
        $this->vehicleMock->method('all')->willReturn($vehicles);

        $response = $this->controller->getAll($this->requestMock, $this->responseMock);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertStringContainsString('B123XYZ', $body);
    }

    public function testCreateReturnsCreated(): void {
        $data = ['numar_inmatriculare' => 'B123XYZ'];
        $this->requestMock->method('getParsedBody')->willReturn($data);

        $this->vehicleMock->expects($this->once())->method('save')->with($data);

        $response = $this->controller->create($this->requestMock, $this->responseMock);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testUpdateReturnsSuccess(): void {
        $vehicle = ['id' => 1, 'numar_inmatriculare' => 'B123XYZ'];
        $this->vehicleMock->method('find')->willReturn($vehicle);
        
        $data = ['numar_inmatriculare' => 'B456XYZ'];
        $this->requestMock->method('getParsedBody')->willReturn($data);

        $this->vehicleMock->expects($this->once())->method('update')->with(1, $data);

        $response = $this->controller->update($this->requestMock, $this->responseMock, ['id' => 1]);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteVehicleReturnSuccess()
    {
        $vehicleId = 1;

        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with($vehicleId)->willReturn(['id' => $vehicleId]);
        $vehicleMock->expects($this->once())->method('delete')->with($vehicleId);

        $request = $this->createMock(Request::class);
        $response = (new ResponseFactory())->createResponse();

        $controller = new VehicleController($vehicleMock);
        $response = $controller->delete($request, $response, ['id' => $vehicleId]);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
    }

    public function testCreateWithValidNumber()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');

        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'B123ABC']);

        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->create($request, $response);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCreateWithInvalidNumber()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->never())->method('save');

        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => '123ABC']);

        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->create($request, $response);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Format invalid', (string)$response->getBody());
    }

    public function testUpdateWithValidVehicle()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->once())->method('update')->with(1, ['numar_inmatriculare' => 'CJ101XYZ']);

        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'CJ101XYZ']);

        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->update($request, $response, ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateWithInvalidNumber()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->never())->method('update');

        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => '12']);

        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->update($request, $response, ['id' => 1]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Format invalid', (string)$response->getBody());
    }

    public function testUpdateWithNonexistentVehicle()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn(['numar_inmatriculare' => 'CJ101XYZ']);

        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->update($request, $response, ['id' => 999]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Nu am gasit autovehiculul', (string)$response->getBody());
    }

    public function testDeleteVehicleFound()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->once())->method('delete')->with(1);

        $request = $this->createMock(Request::class);
        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->delete($request, $response, ['id' => 1]);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
    }

    public function testDeleteVehicleNotFound()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $request = $this->createMock(Request::class);
        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->delete($request, $response, ['id' => 999]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Nu am gasit autovehiculul', (string)$response->getBody());
    }

    public function testGetByIdVehicleFound()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with(1)->willReturn(['id' => 1, 'numar_inmatriculare' => 'B123ABC']);

        $request = $this->createMock(Request::class);
        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->getById($request, $response, ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('B123ABC', (string)$response->getBody());
    }

    public function testGetByIdVehicleNotFound()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with(999)->willReturn(false);

        $request = $this->createMock(Request::class);
        $response = (new ResponseFactory())->createResponse();
        $controller = new VehicleController($vehicleMock);
        $response = $controller->getById($request, $response, ['id' => 999]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Nu am gasit autovehiculul', (string)$response->getBody());
    }

    public function testCreateWithMinValidLicensePlate() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');
    
        $controller = new VehicleController($vehicleMock);
    
        $request = $this->createJsonRequest(['numar_inmatriculare' => 'A12A']);
        $response = $this->createJsonResponse();
    
        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }
    
    public function testCreateWithMaxValidLicensePlate() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');
    
        $controller = new VehicleController($vehicleMock);
        $request = $this->createJsonRequest(['numar_inmatriculare' => 'AB123ABC']);
        $response = $this->createJsonResponse();
    
        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }
    
    public function testCreateWithTooShortLicensePlate() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $controller = new VehicleController($vehicleMock);
    
        $request = $this->createJsonRequest(['numar_inmatriculare' => 'A1']);
        $response = $this->createJsonResponse();
    
        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }
    
    public function testCreateWithTooLongLicensePlate() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $controller = new VehicleController($vehicleMock);
    
        $request = $this->createJsonRequest(['numar_inmatriculare' => 'AB1234ABCD']);
        $response = $this->createJsonResponse();
    
        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }
    
    public function testGetByIdWithZeroId() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with(0)->willReturn(['id' => 0]);
    
        $controller = new VehicleController($vehicleMock);
        $response = $this->createJsonResponse();
        $request = $this->createMock(ServerRequestInterface::class);
    
        $result = $controller->getById($request, $response, ['id' => 0]);
        $this->assertEquals(200, $result->getStatusCode());
    }
    
    public function testGetByIdWithMaxIntId() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with(PHP_INT_MAX)->willReturn(['id' => PHP_INT_MAX]);
    
        $controller = new VehicleController($vehicleMock);
        $response = $this->createJsonResponse();
        $request = $this->createMock(ServerRequestInterface::class);
    
        $result = $controller->getById($request, $response, ['id' => PHP_INT_MAX]);
        $this->assertEquals(200, $result->getStatusCode());
    }
    
    public function testGetByIdWithNegativeId() {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->with(-1)->willReturn(false);
    
        $controller = new VehicleController($vehicleMock);
        $response = $this->createJsonResponse();
        $request = $this->createMock(ServerRequestInterface::class);
    
        $result = $controller->getById($request, $response, ['id' => -1]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Helper methods
    private function createJsonRequest(array $parsedBody): Request
    {
        $request = $this->createMock(Request::class);
        $request->method('getParsedBody')->willReturn($parsedBody);
        return $request;
    }

    private function createJsonResponse(): Response
    {
        $stream = fopen('php://temp', 'r+');
        $body = new \Slim\Psr7\Stream($stream);
        $response = new \Slim\Psr7\Response();
        return $response->withBody($body);
    }
}
