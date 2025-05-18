<?php
declare(strict_types=1);

namespace Tests\Structure;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use App\models\Vehicle;
use App\controllers\VehicleController;

/**
 * Structural (contract) tests – verify public API shape only.
 */
final class VehicleAIStructuralTest extends TestCase
{
    /**
     * @dataProvider classProvider
     */
    public function testPublicAPI(string $fqcn, array $expected): void
    {
        $rc = new ReflectionClass($fqcn);

        $this->assertFalse($rc->isAbstract(), "$fqcn should not be abstract");
        $this->assertFalse($rc->isFinal(),    "$fqcn should not be final");

        foreach ($expected as $method => $spec) {
            $this->assertTrue($rc->hasMethod($method), "$fqcn::$method() missing");

            $rm = $rc->getMethod($method);
            $this->assertTrue($rm->isPublic(), "$fqcn::$method() must be public");

            // parameter count
            $this->assertSame(
                $spec['params'],
                $rm->getNumberOfParameters(),
                "$fqcn::$method() param-count mismatch"
            );

            // declared return type
            $rt = $rm->getReturnType();
            $this->assertNotNull($rt, "$fqcn::$method() missing return type");
            $this->assertSame(
                trim($spec['return']),
                trim((string) $rt),
                "$fqcn::$method() return type mismatch"
            );
        }
    }

    public static function classProvider(): array
    {
        return [
            // Vehicle domain model
            [
                Vehicle::class,
                [
                    'all'    => ['params' => 0, 'return' => 'array'],
                    'find'   => ['params' => 1, 'return' => 'array|false'],
                    'save'   => ['params' => 1, 'return' => 'array|false'],
                    'update' => ['params' => 2, 'return' => 'array|false'],
                    'delete' => ['params' => 1, 'return' => 'void'],
                ],
            ],
            // VehicleController HTTP adapter
            [
                VehicleController::class,
                [
                    'getAll'   => ['params' => 2, 'return' => '\Psr\Http\Message\ResponseInterface'],
                    'getById'  => ['params' => 3, 'return' => '\Psr\Http\Message\ResponseInterface'],
                    'create'   => ['params' => 2, 'return' => '\Psr\Http\Message\ResponseInterface'],
                    'update'   => ['params' => 3, 'return' => '\Psr\Http\Message\ResponseInterface'],
                    'delete'   => ['params' => 3, 'return' => '\Psr\Http\Message\ResponseInterface'],
                ],
            ],
        ];
    }
}