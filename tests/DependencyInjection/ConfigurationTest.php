<?php

namespace GALes\MakerBundle\Tests\DependencyInjection;

use GALes\MakerBundle\DependencyInjection\Configuration;
use GALes\MakerBundle\DependencyInjection\GALesMakerExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * #25: gales_maker.crud configura de dónde salen los roles sugeridos y cuáles tienen acceso siempre.
 */
final class ConfigurationTest extends TestCase
{
    private function procesar(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }

    public function testValoresPorDefecto(): void
    {
        self::assertSame(['roles_source' => null, 'default_roles' => ['ROLE_ADMIN']], $this->procesar([])['crud']);
    }

    public function testAceptaOrigenYRolesFijos(): void
    {
        $crud = $this->procesar(['crud' => [
            'roles_source' => 'App\\Entity\\User::getRolesDetalle',
            'default_roles' => ['ROLE_SUPERVISOR'],
        ]])['crud'];

        self::assertSame('App\\Entity\\User::getRolesDetalle', $crud['roles_source']);
        self::assertSame(['ROLE_SUPERVISOR'], $crud['default_roles']);
    }

    public static function invalidasProvider(): array
    {
        return [
            'origen sin ::'       => [['roles_source' => 'App\\Entity\\User']],
            'rol fijo invalido'   => [['default_roles' => ['ADMIN']]],
            'sin roles fijos'     => [['default_roles' => []]],
        ];
    }

    #[DataProvider('invalidasProvider')]
    public function testConfiguracionInvalida(array $crud): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->procesar(['crud' => $crud]);
    }

    public function testLaExtensionPasaLaConfiguracionAlProveedorDeRoles(): void
    {
        $container = new ContainerBuilder();
        (new GALesMakerExtension())->load([['crud' => ['roles_source' => 'App\\Entity\\User::getRolesDetalle']]], $container);

        $definicion = $container->getDefinition('gales.maker.crud_roles_provider');
        self::assertSame('%security.role_hierarchy.roles%', $definicion->getArgument(0));
        self::assertSame('App\\Entity\\User::getRolesDetalle', $definicion->getArgument(1));
        self::assertSame(['ROLE_ADMIN'], $definicion->getArgument(2));
    }
}
