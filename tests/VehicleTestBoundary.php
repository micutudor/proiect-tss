<?php

namespace Tests\Controllers;

use App\controllers\VehicleController;
use App\models\Vehicle;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class VehicleTestBoundary extends TestCase
{
    private Vehicle $vehicle;
    private VehicleController $controller;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->vehicle = $this->createMock(Vehicle::class);
        $this->controller = new VehicleController($this->vehicle);
        $this->responseFactory = new ResponseFactory();
    }

    /**
     * Helper: create a request with parsed body
     */
    private function createRequest(array $parsedBody = []): ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/');
        return $request->withParsedBody($parsedBody);
    }

    /**
     * Helper: create a fresh response
     */
    private function createResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse();
    }

    // Test create() with minimum valid registration number 
    public function testCreateWithMinValidNumberPlate(): void
    {
        $this->vehicle->expects($this->once())->method('save');

        $request = $this->createRequest(['numar_inmatriculare' => 'B12A']);
        $response = $this->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertSame(201, $result->getStatusCode());
    }

    // Test create() with maximum valid registration number 
    public function testCreateWithMaxValidNumberPlate(): void
    {
        $this->vehicle->expects($this->once())->method('save');

        $request = $this->createRequest(['numar_inmatriculare' => 'AB123ABC']);
        $response = $this->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertSame(201, $result->getStatusCode());
    }

    // Test create() with invalid (too short) registration number
    public function testCreateWithTooShortNumberPlate(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'B1A']);
        $response = $this->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertSame(400, $result->getStatusCode());
    }

    // Test create() with invalid (too long) registration number
    public function testCreateWithTooLongNumberPlate(): void
    {
        $request = $this->createRequest(['numar_inmatriculare' => 'B1234AB']);
        $response = $this->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertSame(400, $result->getStatusCode());
    }

    // Test getById() with minimum existing ID
    public function testGetByIdWithMinExistingId(): void
    {
        $this->vehicle->method('find')->willReturn(['id' => 1]);

        $request = $this->createRequest();
        $response = $this->createResponse();
        $result = $this->controller->getById($request, $response, ['id' => 1]);

        $this->assertSame(200, $result->getStatusCode());
    }

    // Test getById() with non-existing ID (zero)
    public function testGetByIdWithBelowMinId(): void
    {
        $this->vehicle->method('find')->willReturn(false);

        $request = $this->createRequest();
        $response = $this->createResponse();
        $result = $this->controller->getById($request, $response, ['id' => 0]);

        $this->assertSame(404, $result->getStatusCode());
    }

    // Test delete() with max existing ID
    public function testDeleteWithMaxExistingId(): void
    {
        $this->vehicle->method('find')->willReturn(['id' => 100]);
        $this->vehicle->expects($this->once())->method('delete')->with(100);

        $request = $this->createRequest();
        $response = $this->createResponse();
        $result = $this->controller->delete($request, $response, ['id' => 100]);

        $this->assertSame(204, $result->getStatusCode());
    }

    // Test delete() with non-existing ID above max
    public function testDeleteWithAboveMaxId(): void
    {
        $this->vehicle->method('find')->willReturn(false);

        $request = $this->createRequest();
        $response = $this->createResponse();
        $result = $this->controller->delete($request, $response, ['id' => 101]);

        $this->assertSame(404, $result->getStatusCode());
    }
}
