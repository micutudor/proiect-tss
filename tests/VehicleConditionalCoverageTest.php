<?php
use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleConditionalCoverageTest extends TestCase
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

    // Condition: !$vehicle === true
    public function testGetById_VehicleNotFound_EvaluatesConditionTrue()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Condition: !$vehicle === false
    public function testGetById_VehicleFound_EvaluatesConditionFalse()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->getById($request, $response, ['id' => 1]);
        $this->assertEquals(200, $result->getStatusCode());
    }

    // Condition: !preg_match(...) === true (invalid license plate)
    public function testCreate_InvalidLicensePlate_EvaluatesConditionTrue()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->never())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'INVALID']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Condition: !preg_match(...) === false (valid license plate)
    public function testCreate_ValidLicensePlate_EvaluatesConditionFalse()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->expects($this->once())->method('save');

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createMockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals(201, $result->getStatusCode());
    }

    // Condition 1: !$vehicle === true
    public function testUpdate_VehicleNotFound_EvaluatesConditionTrue()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'B123XYZ']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 999]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // Condition 1: !$vehicle === false, Condition 2: !preg_match(...) === true
    public function testUpdate_FoundButInvalidLicense_EvaluatesSecondConditionTrue()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(['id' => 1]);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMockRequest(['numar_inmatriculare' => 'WRONG123']);
        $response = $this->createMockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals(400, $result->getStatusCode());
    }

    // Both conditions false: vehicle found, license valid
    public function testUpdate_ValidConditions_EvaluatesBothConditionsFalse()
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

    // delete: !$vehicle === true
    public function testDelete_VehicleNotFound_EvaluatesConditionTrue()
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn(false);

        $controller = new VehicleController($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMockResponse();

        $result = $controller->delete($request, $response, ['id' => 42]);
        $this->assertEquals(404, $result->getStatusCode());
    }

    // delete: !$vehicle === false
    public function testDelete_VehicleFound_EvaluatesConditionFalse()
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
