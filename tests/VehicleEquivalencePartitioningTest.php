<?php
use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleEquivalencePartitioningTest extends TestCase
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

    // Valid partition: Creating vehicle with valid license plate format
    public function testCreateWithValidLicensePlate()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B682SPM']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }

    // Invalid partition: Creating vehicle with invalid license plate format
    public function testCreateWithInvalidLicensePlate()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->never())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => '999XYZ']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Valid partition: Getting existing vehicle by ID
    public function testGetByIdWithValidId()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1, 'numar_inmatriculare' => 'B682SPM']);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    // Invalid partition: Getting non-existent vehicle by ID
    public function testGetByIdWithInvalidId()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Valid partition: Updating existing vehicle with valid license plate
    public function testUpdateWithValidIdAndValidLicensePlate()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->once())->method('update');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'VS66MTV']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    // Invalid partition: Updating with invalid license plate format
    public function testUpdateWithValidIdAndInvalidLicensePlate()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);
        $vehicleMock->expects($this->never())->method('update');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => '888AAA']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Invalid partition: Updating vehicle that does not exist
    public function testUpdateWithInvalidId()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);
        $vehicleMock->expects($this->never())->method('update');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B682ZZZ']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Valid partition: Deleting vehicle that exists
    public function testDeleteWithValidId()
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

    // Invalid partition: Deleting vehicle that does not exist
    public function testDeleteWithInvalidId()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);
        $vehicleMock->expects($this->never())->method('delete');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->delete($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }
}
