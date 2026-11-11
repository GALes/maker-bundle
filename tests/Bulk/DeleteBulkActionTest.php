<?php

namespace GALes\MakerBundle\Tests\Bulk;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use GALes\MakerBundle\Bulk\DeleteBulkAction;
use PHPUnit\Framework\TestCase;

final class DeleteBulkActionTest extends TestCase
{
    public function testRemuevePorCadaEntidadYDevuelveCantidadYMensaje(): void
    {
        $a = new \stdClass();
        $b = new \stdClass();
        $removed = [];
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::exactly(2))->method('remove')->willReturnCallback(function (object $e) use (&$removed): void {
            $removed[] = $e;
        });
        $manager->expects(self::never())->method('flush');
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with(\stdClass::class)->willReturn($manager);

        $result = (new DeleteBulkAction($registry))->execute([$a, $b]);

        self::assertSame([$a, $b], $removed);
        self::assertSame(2, $result->affected);
        self::assertSame('El/Los registro/s se ha/n eliminado exitosamente', $result->message);
    }

    public function testMetadatos(): void
    {
        $action = new DeleteBulkAction($this->createMock(ManagerRegistry::class));

        self::assertSame('delete', $action->getName());
        self::assertSame('ELIMINAR', $action->getLabel());
        self::assertSame('¿Está seguro?', $action->getConfirmMessage());
        self::assertTrue($action->supports(\stdClass::class));
    }
}
