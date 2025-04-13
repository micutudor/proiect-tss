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
}
