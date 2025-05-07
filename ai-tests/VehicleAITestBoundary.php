<?php

namespace Tests\Controllers;

use App\controllers\VehicleController;
use App\models\Vehicle;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class VehicleAITestBoundary extends TestCase
{
    private Vehicle $vehicleModel;
    private VehicleController $controller;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->vehicleModel = $this->createMock(Vehicle::class);
        $this->controller = new VehicleController($this->vehicleModel);
        $this->responseFactory = new ResponseFactory();
    }

    public function vehicleRegistrationProvider(): array
    {
        return [
            'minimum valid plate' => ['B12A', true, 201],
            'maximum valid plate' => ['AB123ABC', true, 201],
            'too short plate' => ['B1A', false, 400],
            'too long plate' => ['B1234AB', false, 400],
            'empty plate' => ['', false, 400],
            'plate with special chars' => ['B12@#$', false, 400],
        ];
    }

    public function vehicleIdProvider(): array
    {
        return [
            'minimum existing ID' => [1, true, 200],
            'non-existing zero ID' => [0, false, 404],
            'large existing ID' => [100, true, 200],
            'non-existing large ID' => [101, false, 404],
            'negative ID' => [-1, false, 404],
        ];
    }

    /**
     * @dataProvider vehicleRegistrationProvider
     */
    public function testVehicleCreationBoundaries(
        string $plateNumber,
        bool $shouldSave,
        int $expectedStatusCode
    ): void {
        if ($shouldSave) {
            $this->vehicleModel->expects($this->once())->method('save');
        } else {
            $this->vehicleModel->expects($this->never())->method('save');
        }

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['numar_inmatriculare' => $plateNumber]);
        
        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);
        
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    /**
     * @dataProvider vehicleIdProvider
     */
    public function testGetVehicleByIdBoundaries(
        int $id,
        bool $shouldExist,
        int $expectedStatusCode
    ): void {
        $this->vehicleModel->method('find')
            ->willReturn($shouldExist ? ['id' => $id] : false);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $response = $this->responseFactory->createResponse();
        
        $result = $this->controller->getById($request, $response, ['id' => $id]);
        
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
    }

    public function testSuccessfulVehicleDeletion(): void
    {
        $id = 100;
        $this->vehicleModel->method('find')->willReturn(['id' => $id]);
        $this->vehicleModel->expects($this->once())
            ->method('delete')
            ->with($id);

        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/');
        $response = $this->responseFactory->createResponse();
        
        $result = $this->controller->delete($request, $response, ['id' => $id]);
        
        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testFailedVehicleDeletion(): void
    {
        $id = 999;
        $this->vehicleModel->method('find')->willReturn(false);
        $this->vehicleModel->expects($this->never())->method('delete');

        $request = (new ServerRequestFactory())->createServerRequest('DELETE', '/');
        $response = $this->responseFactory->createResponse();
        
        $result = $this->controller->delete($request, $response, ['id' => $id]);
        
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testVehicleCreationWithMissingPlateNumber(): void
    {
        $this->vehicleModel->expects($this->never())->method('save');

        $request = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody([]); // No plate number provided
            
        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);
        
        $this->assertEquals(400, $result->getStatusCode());
    }
}