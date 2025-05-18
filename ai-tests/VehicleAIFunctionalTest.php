<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\controllers\VehicleController;
use App\models\Vehicle;
use App\config\Database;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Functional tests that hit a real MySQL test database.
 *
 * Required ENV vars (defaults in parentheses):
 *   TEST_DB_DSN   (mysql:host=127.0.0.1;dbname=test;charset=utf8mb4)
 *   TEST_DB_USER  (root)
 *   TEST_DB_PASS  ('')
 */
final class VehicleAIFunctionalTest extends TestCase
{
    private PDO $pdo;
    private VehicleController $controller;

    /* ---------- helpers ---------- */

    private static function mysqlPDO(): PDO
    {
        $dsn  = getenv('TEST_DB_DSN')  ?: 'mysql:host=127.0.0.1;dbname=parcauto;charset=utf8mb4';
        $user = getenv('TEST_DB_USER') ?: 'root';
        $pass = getenv('TEST_DB_PASS') ?: '';

        return new PDO(
            $dsn,
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private static function ensureSchema(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS autovehicule (
                id INT AUTO_INCREMENT PRIMARY KEY,
                marca VARCHAR(100) NOT NULL,
                model VARCHAR(100) NOT NULL,
                data_expirare_itp DATE NULL,
                data_expirare_rovinieta DATE NULL,
                data_expirare_trusa DATE NULL,
                data_expirare_rca DATE NULL,
                numar_inmatriculare VARCHAR(15) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    private function req(string $method, string $uri, array $body = [])
    {
        $request  = (new ServerRequestFactory())->createServerRequest($method, $uri)
                                                ->withParsedBody($body);
        $response = (new ResponseFactory())->createResponse();
        return [$request, $response];
    }

    /* ---------- set-up / tear-down ---------- */

    protected function setUp(): void
    {
        $this->pdo = self::mysqlPDO();
        self::ensureSchema($this->pdo);

        // Isolate each test inside a transaction; changes are rolled back.
        $this->pdo->beginTransaction();
        $this->pdo->exec('DELETE FROM autovehicule');

        // Stub the Database wrapper so Vehicle uses this PDO.
        $dbStub = $this->createStub(Database::class);
        $dbStub->method('getConnection')->willReturn($this->pdo);

        $vehicle        = new Vehicle($dbStub);
        $this->controller = new VehicleController($vehicle);
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /* ---------- tests ---------- */

    public function testGetAllEmpty(): void
    {
        [$req, $resp] = $this->req('GET', '/vehicles');
        $resp = $this->controller->getAll($req, $resp);

        $this->assertSame(200, $resp->getStatusCode());
        $this->assertSame('application/json', $resp->getHeaderLine('Content-Type'));
        $this->assertSame('[]', (string) $resp->getBody());
    }

    public function testCreateRejectsBadPlate(): void
    {
        [$req, $resp] = $this->req('POST', '/vehicles', [
            'marca'               => 'Ford',
            'model'               => 'Focus',
            'numar_inmatriculare' => 'BAD-PLATE',
        ]);
        $resp = $this->controller->create($req, $resp);

        $this->assertSame(400, $resp->getStatusCode());
        $this->assertStringContainsString('Format invalid', (string) $resp->getBody());
    }

    public function testFullCRUD(): void
    {
        /* ---- create ---- */
        [$cReq, $cResp] = $this->req('POST', '/vehicles', [
            'marca'               => 'Audi',
            'model'               => 'A4',
            'numar_inmatriculare' => 'B123AUD',
        ]);
        $cResp = $this->controller->create($cReq, $cResp);
        $this->assertSame(201, $cResp->getStatusCode());

        /* ---- list ---- */
        [$lReq, $lResp] = $this->req('GET', '/vehicles');
        $lResp = $this->controller->getAll($lReq, $lResp);
        $list  = json_decode((string) $lResp->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(1, $list);
        $id = $list[0]['id'];

        /* ---- update ---- */
        [$uReq, $uResp] = $this->req('PUT', "/vehicles/{$id}", [
            'marca'               => 'Audi',
            'model'               => 'A6',
            'numar_inmatriculare' => 'B123AUD',
        ]);
        $uResp = $this->controller->update($uReq, $uResp, ['id' => $id]);
        $this->assertSame(200, $uResp->getStatusCode());

        /* ---- show ---- */
        [$sReq, $sResp] = $this->req('GET', "/vehicles/{$id}");
        $sResp   = $this->controller->getById($sReq, $sResp, ['id' => $id]);
        $vehicle = json_decode((string) $sResp->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('A6', $vehicle['model']);

        /* ---- delete ---- */
        [$dReq, $dResp] = $this->req('DELETE', "/vehicles/{$id}");
        $dResp = $this->controller->delete($dReq, $dResp, ['id' => $id]);
        $this->assertSame(204, $dResp->getStatusCode());

        /* ---- verify gone ---- */
        [$gReq, $gResp] = $this->req('GET', "/vehicles/{$id}");
        $gResp = $this->controller->getById($gReq, $gResp, ['id' => $id]);
        $this->assertSame(404, $gResp->getStatusCode());
    }
}