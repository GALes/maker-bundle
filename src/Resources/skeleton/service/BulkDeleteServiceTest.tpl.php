<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $service_full_class_name ?>;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class <?= $class_name ?> extends TestCase
{
    private function crearServicio(array $existentes, callable $isGranted, ?EntityManagerInterface &$em = null): <?= $service_class_name ?> {
        $repositorio = $this->createMock(EntityRepository::class);
        $repositorio->method('findBy')->willReturnCallback(
            fn (array $criterio) => array_values(array_filter(
                $existentes,
                fn ($e) => in_array($e->id, $criterio['id'], true)
            ))
        );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repositorio);
        $em->method('wrapInTransaction')->willReturnCallback(fn (callable $fn) => $fn());

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback($isGranted);

        return new <?= $service_class_name ?>($em, $security);
    }

    private function entidad(int $id): object
    {
        return (object) ['id' => $id];
    }

    public function testBorraTodoElLoteConUnSoloFlush(): void
    {
        $a = $this->entidad(1);
        $b = $this->entidad(2);
        $servicio = $this->crearServicio([$a, $b], fn () => true, $em);

        $em->expects($this->exactly(2))->method('remove');
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('wrapInTransaction');

        $this->assertSame(2, $servicio->eliminar(\stdClass::class, [1, 2], 'x_eliminar'));
    }

    public function testUnaDenegacionLanza403SinBorrarNada(): void
    {
        $a = $this->entidad(1);
        $b = $this->entidad(2);
        $servicio = $this->crearServicio([$a, $b], fn ($permiso, $entidad) => $entidad !== $b, $em);

        $em->expects($this->never())->method('remove');
        $em->expects($this->never())->method('flush');

        $this->expectException(AccessDeniedException::class);
        $servicio->eliminar(\stdClass::class, [1, 2], 'x_eliminar');
    }

    public function testIdsInexistentesSeIgnoran(): void
    {
        $a = $this->entidad(1);
        $servicio = $this->crearServicio([$a], fn () => true, $em);

        $em->expects($this->once())->method('remove')->with($a);
        $em->expects($this->once())->method('flush');

        $this->assertSame(1, $servicio->eliminar(\stdClass::class, [1, 99, 100], 'x_eliminar'));
    }

    public function testLoteSinRegistrosExistentesNoHaceNada(): void
    {
        $servicio = $this->crearServicio([], fn () => true, $em);

        $em->expects($this->never())->method('remove');
        $em->expects($this->never())->method('flush');

        $this->assertSame(0, $servicio->eliminar(\stdClass::class, [99], 'x_eliminar'));
        $this->assertSame(0, $servicio->eliminar(\stdClass::class, [], 'x_eliminar'));
    }

    public function testSinPermisoSoloAdminBorra(): void
    {
        $a = $this->entidad(1);

        $servicio = $this->crearServicio([$a], fn ($rol) => 'ROLE_ADMIN' === $rol, $em);
        $this->assertSame(1, $servicio->eliminar(\stdClass::class, [1]));

        $servicio = $this->crearServicio([$a], fn () => false, $em);
        $em->expects($this->never())->method('remove');
        $this->expectException(AccessDeniedException::class);
        $servicio->eliminar(\stdClass::class, [1]);
    }
}
