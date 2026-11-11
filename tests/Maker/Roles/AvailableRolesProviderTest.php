<?php

namespace GALes\MakerBundle\Tests\Maker\Roles;

use GALes\MakerBundle\Maker\Roles\AvailableRolesProvider;
use GALes\MakerBundle\Tests\Maker\Roles\Fixtures\UsuarioConRoles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * #25: roles sugeridos al elegir quién accede al CRUD, sin acoplar el bundle a una entidad concreta.
 */
final class AvailableRolesProviderTest extends TestCase
{
    private const JERARQUIA = [
        'ROLE_ADMIN'        => ['ROLE_ADMIN', 'ROLE_FILEDOWNLOAD', 'ROLE_REPORTE'],
        'ROLE_FILEDOWNLOAD' => ['ROLE_FILEDOWNLOAD'],
        'ROLE_SUPERVISOR'   => ['ROLE_USER', 'ROLE_ALLOWED_TO_SWITCH'],
    ];

    public function testSinOrigenUsaClavesYValoresDeLaJerarquia(): void
    {
        $provider = new AvailableRolesProvider(self::JERARQUIA);

        self::assertSame(['ROLE_FILEDOWNLOAD', 'ROLE_REPORTE', 'ROLE_SUPERVISOR'], $provider->getSuggestedRoles());
        self::assertNull($provider->getSourceError());
    }

    public function testOrigenMetodoEstaticoVaPrimeroYUsaLosValores(): void
    {
        $provider = new AvailableRolesProvider(self::JERARQUIA, UsuarioConRoles::class.'::getRolesDetalle');

        self::assertSame(['ROLE_REPORTE', 'ROLE_CONSULTA', 'ROLE_FILEDOWNLOAD', 'ROLE_SUPERVISOR'], $provider->getSuggestedRoles());
    }

    public function testOrigenConstante(): void
    {
        $provider = new AvailableRolesProvider([], UsuarioConRoles::class.'::ROLES_FOR_CHOICES');

        self::assertSame(['ROLE_REPORTE'], $provider->getSuggestedRoles());
    }

    public static function origenesInvalidosProvider(): array
    {
        return [
            'clase inexistente'    => ['App\\NoExiste::getRolesDetalle'],
            'miembro inexistente'  => [UsuarioConRoles::class.'::noExiste'],
            'metodo de instancia'  => [UsuarioConRoles::class.'::getRolesDeInstancia'],
            'no devuelve array'    => [UsuarioConRoles::class.'::getTexto'],
        ];
    }

    #[DataProvider('origenesInvalidosProvider')]
    public function testOrigenInvalidoSeInformaYNoCorta(string $origen): void
    {
        $provider = new AvailableRolesProvider(['ROLE_ADMIN' => ['ROLE_REPORTE']], $origen);

        self::assertSame(['ROLE_REPORTE'], $provider->getSuggestedRoles());
        self::assertStringContainsString($origen, (string) $provider->getSourceError());
    }

    public function testRolesFijosConfigurables(): void
    {
        $provider = new AvailableRolesProvider(self::JERARQUIA, null, ['role_supervisor']);

        self::assertSame(['ROLE_SUPERVISOR'], $provider->getDefaultRoles());
        self::assertSame(['ROLE_ADMIN', 'ROLE_FILEDOWNLOAD', 'ROLE_REPORTE'], $provider->getSuggestedRoles());
    }
}
