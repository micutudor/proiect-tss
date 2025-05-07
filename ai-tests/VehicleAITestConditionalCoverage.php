<?php

use PHPUnit\Framework\TestCase;
use App\controllers\VehicleController;
use App\models\Vehicle;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class VehicleAITestConditionalCoverage extends TestCase
{
    private function createControllerWithMock(Vehicle $vehicle): VehicleController
    {
        return new VehicleController($vehicle);
    }

    private function mockRequest(array $body = []): ServerRequestInterface
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        return $request;
    }

    private function mockResponse(): ResponseInterface
    {
        $responseFactory = new ResponseFactory();
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream('');
        return $responseFactory->createResponse()->withBody($body);
    }

    /**
     * @dataProvider getByIdProvider
     */
    public function testGetByIdVariants($findResult, $expectedStatus)
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn($findResult);

        $controller = $this->createControllerWithMock($vehicleMock);
        $response = $this->mockResponse();
        $request = $this->createMock(ServerRequestInterface::class);

        $result = $controller->getById($request, $response, ['id' => 1]);
        $this->assertEquals($expectedStatus, $result->getStatusCode());
    }

    public function getByIdProvider(): array
    {
        return [
            'Not Found' => [false, 404],
            'Found' => [['id' => 1], 200],
        ];
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreateVariants(string $plate, bool $isValid)
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        if ($isValid) {
            $vehicleMock->expects($this->once())->method('save');
        } else {
            $vehicleMock->expects($this->never())->method('save');
        }

        $controller = $this->createControllerWithMock($vehicleMock);
        $request = $this->mockRequest(['numar_inmatriculare' => $plate]);
        $response = $this->mockResponse();

        $result = $controller->create($request, $response);
        $this->assertEquals($isValid ? 201 : 400, $result->getStatusCode());
    }

    public function createProvider(): array
    {
        return [
            'Invalid Plate' => ['INVALID', false],
            'Valid Plate' => ['B123XYZ', true],
        ];
    }

    /**
     * @dataProvider updateProvider
     */
    public function testUpdateVariants($findResult, string $plate, $expectUpdate, int $expectedStatus)
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn($findResult);

        if ($expectUpdate) {
            $vehicleMock->expects($this->once())->method('update');
        } else {
            $vehicleMock->expects($this->never())->method('update');
        }

        $controller = $this->createControllerWithMock($vehicleMock);
        $request = $this->mockRequest(['numar_inmatriculare' => $plate]);
        $response = $this->mockResponse();

        $result = $controller->update($request, $response, ['id' => 1]);
        $this->assertEquals($expectedStatus, $result->getStatusCode());
    }

    public function updateProvider(): array
    {
        return [
            'Vehicle Not Found' => [false, 'B123XYZ', false, 404],
            'Invalid Plate Format' => [['id' => 1], 'WRONG123', false, 400],
            'Valid Update' => [['id' => 1], 'AB123CD', true, 200],
        ];
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDeleteVariants($findResult, $expectDelete, int $expectedStatus)
    {
        $vehicleMock = $this->createMock(Vehicle::class);
        $vehicleMock->method('find')->willReturn($findResult);

        if ($expectDelete) {
            $vehicleMock->expects($this->once())->method('delete');
        } else {
            $vehicleMock->expects($this->never())->method('delete');
        }

        $controller = $this->createControllerWithMock($vehicleMock);
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->mockResponse();

        $result = $controller->delete($request, $response, ['id' => 10]);
        $this->assertEquals($expectedStatus, $result->getStatusCode());
    }

    public function deleteProvider(): array
    {
        return [
            'Vehicle Not Found' => [false, false, 404],
            'Vehicle Found and Deleted' => [['id' => 1], true, 204],
        ];
    }
}
