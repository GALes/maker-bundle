<?php

namespace GALes\MakerBundle\Tests\Maker\Routing;

use GALes\MakerBundle\Maker\Routing\RoutePrefixNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * #9: el prefijo de ruta del CRUD se normaliza y se valida antes de escribirlo en #[Route].
 */
final class RoutePrefixNormalizerTest extends TestCase
{
    public static function validosProvider(): array
    {
        return [
            'null'                 => [null, ''],
            'vacio'                => ['', ''],
            'solo espacios'        => ['  ', ''],
            'solo barra'           => ['/', ''],
            'con barra inicial'    => ['/admin', '/admin'],
            'sin barra inicial'    => ['admin', '/admin'],
            'con barra final'      => ['/admin/', '/admin'],
            'barras repetidas'     => ['//admin//panel/', '/admin/panel'],
            'anidado'              => ['/backoffice/admin', '/backoffice/admin'],
            'guion y guion bajo'   => ['/panel-admin_2', '/panel-admin_2'],
            'parametro de locale'  => ['/{_locale}/admin', '/{_locale}/admin'],
            'espacios alrededor'   => [' /admin ', '/admin'],
        ];
    }

    #[DataProvider('validosProvider')]
    public function testNormaliza(?string $entrada, string $esperado): void
    {
        self::assertSame($esperado, RoutePrefixNormalizer::normalize($entrada));
    }

    public static function invalidosProvider(): array
    {
        return [
            'comillas'        => ['/ad"min'],
            'espacio interno' => ['/ad min'],
            'backslash'       => ['\\admin'],
            'php inyectado'   => ['/a$b'],
            'parentesis'      => ['/admin()'],
            'acentos'         => ['/administración'],
        ];
    }

    #[DataProvider('invalidosProvider')]
    public function testRechazaCaracteresInvalidos(string $entrada): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no es un prefijo de ruta válido');

        RoutePrefixNormalizer::normalize($entrada);
    }

    public function testApplyAnteponeElPrefijo(): void
    {
        self::assertSame('/admin/solicitud', RoutePrefixNormalizer::apply('/admin', '/solicitud'));
        self::assertSame('/admin/nota/pedido', RoutePrefixNormalizer::apply('/admin', '/nota/pedido'));
    }

    public function testApplySinPrefijoDejaLaRutaIgual(): void
    {
        self::assertSame('/solicitud', RoutePrefixNormalizer::apply('', '/solicitud'));
        self::assertSame('/nota/pedido', RoutePrefixNormalizer::apply('', 'nota/pedido'));
    }
}
