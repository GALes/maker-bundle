<?php

namespace GALes\MakerBundle\Tests\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use GALes\MakerBundle\Bulk\BulkActionRegistry;
use GALes\MakerBundle\Bulk\BulkActionRunner;
use GALes\MakerBundle\Bulk\UnknownBulkActionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class BulkActionRunnerTest extends TestCase
{
    private FakeBulkAction $action;
    private EntityManagerInterface&MockObject $em;
    private EntityRepository&MockObject $repository;
    private Security&MockObject $security;
    private BulkActionRunner $runner;

    /** @var array<int, object> */
    private array $found = [];
    private int $transactions = 0;
    /** @var string[] */
    private array $idFields = ['id'];
    /** @var mixed */
    private $lastCriteria;

    protected function setUp(): void
    {
        $this->action = new FakeBulkAction('fake');
        $this->found = [new \stdClass(), new \stdClass()];
        $this->transactions = 0;
        $this->idFields = ['id'];

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getIdentifierFieldNames')->willReturnCallback(fn () => $this->idFields);

        $this->repository = $this->createMock(EntityRepository::class);
        $this->repository->method('findBy')->willReturnCallback(function (array $criteria) {
            $this->lastCriteria = $criteria;

            return $this->found;
        });

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getClassMetadata')->willReturn($metadata);
        $this->em->method('getRepository')->willReturn($this->repository);
        $this->em->method('wrapInTransaction')->willReturnCallback(function (callable $fn) {
            ++$this->transactions;

            return $fn();
        });

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->em);

        $this->security = $this->createMock(Security::class);
        $this->security->method('isGranted')->willReturn(true);

        $this->runner = new BulkActionRunner($registry, $this->security, new BulkActionRegistry([$this->action, new FakeBulkAction('ajena', false)]));
    }

    public function testEjecutaTodoElLoteEnUnaSolaTransaccion(): void
    {
        $result = $this->runner->run(\stdClass::class, 'fake', [1, '2'], ['fake' => 'permiso']);

        self::assertSame(2, $result->affected);
        self::assertSame(1, $this->transactions);
        self::assertSame(1, $this->action->executions);
        self::assertSame($this->found, $this->action->received);
        self::assertSame(['id' => [1, '2']], $this->lastCriteria);
    }

    public function testUnaDenegacionLanzaAccessDeniedSinEjecutarNada(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnOnConsecutiveCalls(true, false);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->em);
        $runner = new BulkActionRunner($registry, $security, new BulkActionRegistry([$this->action]));

        try {
            $runner->run(\stdClass::class, 'fake', [1, 2], ['fake' => 'permiso']);
            self::fail('Debía lanzar AccessDeniedException');
        } catch (AccessDeniedException) {
            self::assertSame(0, $this->action->executions);
            self::assertSame(0, $this->transactions);
        }
    }

    public function testAccionFueraDelMapaLanzaUnknown(): void
    {
        $this->expectException(UnknownBulkActionException::class);
        $this->runner->run(\stdClass::class, 'fake', [1], ['otra' => 'permiso']);
    }

    public function testAccionQueNoSoportaLaEntidadLanzaUnknown(): void
    {
        $this->expectException(UnknownBulkActionException::class);
        $this->runner->run(\stdClass::class, 'ajena', [1], ['ajena' => 'permiso']);
    }

    public function testAccionInexistenteEnElRegistryLanzaUnknown(): void
    {
        $this->expectException(UnknownBulkActionException::class);
        $this->runner->run(\stdClass::class, 'nada', [1], ['nada' => 'permiso']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('permisosInvalidos')]
    public function testPermisoVacioONoStringLanzaInvalidArgument(mixed $permiso): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->runner->run(\stdClass::class, 'fake', [1], ['fake' => $permiso]);
    }

    public static function permisosInvalidos(): iterable
    {
        yield 'vacío' => [''];
        yield 'null' => [null];
        yield 'array' => [['x']];
    }

    public function testIdsVaciosSinTransaccion(): void
    {
        $result = $this->runner->run(\stdClass::class, 'fake', [], ['fake' => 'permiso']);

        self::assertSame(0, $result->affected);
        self::assertSame(0, $this->transactions);
        self::assertSame(0, $this->action->executions);
    }

    public function testIdsRepetidosYNoEscalaresSeSanean(): void
    {
        $this->runner->run(\stdClass::class, 'fake', [1, 1, '', null, [2], new \stdClass(), 1.5, '3', 1], ['fake' => 'permiso']);

        self::assertSame(['id' => [1, '3']], $this->lastCriteria);
    }

    public function testIdsInexistentesNoEjecutanNiAbrenTransaccion(): void
    {
        $this->found = [];

        $result = $this->runner->run(\stdClass::class, 'fake', [99], ['fake' => 'permiso']);

        self::assertSame(0, $result->affected);
        self::assertSame(0, $this->transactions);
        self::assertSame(0, $this->action->executions);
    }

    public function testClaveCompuestaLanzaLogicException(): void
    {
        $this->idFields = ['a', 'b'];

        $this->expectException(\LogicException::class);
        $this->runner->run(\stdClass::class, 'fake', [1], ['fake' => 'permiso']);
    }
}
