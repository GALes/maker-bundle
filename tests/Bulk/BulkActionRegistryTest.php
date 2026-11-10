<?php

namespace GALes\MakerBundle\Tests\Bulk;

use GALes\MakerBundle\Bulk\BulkActionRegistry;
use GALes\MakerBundle\Bulk\UnknownBulkActionException;
use PHPUnit\Framework\TestCase;

final class BulkActionRegistryTest extends TestCase
{
    public function testNombreDuplicadoLanzaLogicException(): void
    {
        $this->expectException(\LogicException::class);
        new BulkActionRegistry([new FakeBulkAction('a'), new FakeBulkAction('a')]);
    }

    public function testNombreInvalidoLanzaLogicException(): void
    {
        $this->expectException(\LogicException::class);
        new BulkActionRegistry([new FakeBulkAction('Mala-Accion')]);
    }

    public function testGetDevuelveLaAccionYFallaSiNoExisteONoSoporta(): void
    {
        $ok = new FakeBulkAction('ok');
        $registry = new BulkActionRegistry([$ok, new FakeBulkAction('no', false)]);

        self::assertSame($ok, $registry->get('ok', \stdClass::class));

        foreach (['inexistente', 'no'] as $name) {
            try {
                $registry->get($name, \stdClass::class);
                self::fail('Debía lanzar UnknownBulkActionException');
            } catch (UnknownBulkActionException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testDescribeRespetaElOrdenYOmiteInexistentesYNoSoportadas(): void
    {
        $registry = new BulkActionRegistry([new FakeBulkAction('a'), new FakeBulkAction('b'), new FakeBulkAction('c', false)]);

        self::assertSame([
            ['name' => 'b', 'label' => 'B', 'confirm' => 'Confirmar b'],
            ['name' => 'a', 'label' => 'A', 'confirm' => 'Confirmar a'],
        ], $registry->describe(['b', 'x', 'c', 'a'], \stdClass::class));
    }
}
