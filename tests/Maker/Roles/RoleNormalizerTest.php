<?php

namespace GALes\MakerBundle\Tests\Maker\Roles;

use GALes\MakerBundle\Maker\Roles\RoleNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * #25: los roles elegidos se normalizan y se validan antes de escribirse en el código generado.
 */
final class RoleNormalizerTest extends TestCase
{
    public function testNormalizaMayusculasEspaciosComasYRepetidos(): void
    {
        self::assertSame(
            ['ROLE_REPORTE', 'ROLE_CONSULTA', 'ROLE_X_1'],
            RoleNormalizer::normalize([' role_reporte , ROLE_CONSULTA', 'ROLE_REPORTE', '', 'role_x_1'])
        );
    }

    public function testNuloOVacioDevuelveListaVacia(): void
    {
        self::assertSame([], RoleNormalizer::normalize(null));
        self::assertSame([], RoleNormalizer::normalize(''));
        self::assertSame([], RoleNormalizer::normalize([]));
    }

    public static function invalidosProvider(): array
    {
        return [
            'sin prefijo'      => ['REPORTE'],
            'solo prefijo'     => ['ROLE_'],
            'comilla'          => ["ROLE_A'B"],
            'espacio interno'  => ['ROLE_A B'],
            'guion medio'      => ['ROLE_A-B'],
        ];
    }

    #[DataProvider('invalidosProvider')]
    public function testRolInvalidoLanzaExcepcion(string $valor): void
    {
        $this->expectException(\InvalidArgumentException::class);

        RoleNormalizer::normalize($valor);
    }

    public function testMergePoneLosFijosPrimeroSinRepetir(): void
    {
        self::assertSame(['ROLE_ADMIN', 'ROLE_REPORTE'], RoleNormalizer::merge(['ROLE_ADMIN'], ['ROLE_REPORTE', 'ROLE_ADMIN']));
    }
}
