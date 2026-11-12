<?php

namespace GALes\MakerBundle\Tests\Skeleton;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * #25: el Voter generado lleva los roles elegidos y los atributos ACCEDER y CREAR; el controlador los usa.
 */
final class SecuritySkeletonsTest extends TestCase
{
    private const BASE = __DIR__.'/../../src/Resources/skeleton/';

    private static function render(string $template, array $vars): string
    {
        extract($vars);
        ob_start();
        include self::BASE.$template;

        return (string) ob_get_clean();
    }

    private static function voter(string $namespace): string
    {
        return self::render('security/Voter.tpl.php', [
            'namespace' => $namespace,
            'class_name' => 'ProductVoter',
            'entity_full_class_name' => $namespace.'\\Product',
            'entity_class_name' => 'Product',
            'permission_prefix' => 'product',
            'roles' => ['ROLE_ADMIN', 'ROLE_REPORTE'],
        ]);
    }

    private static function assertPhpValido(string $codigo): void
    {
        $archivo = tempnam(sys_get_temp_dir(), 'skel');
        file_put_contents($archivo, $codigo);
        exec('php -l '.escapeshellarg($archivo).' 2>&1', $salida, $codigoSalida);
        unlink($archivo);

        self::assertSame(0, $codigoSalida, implode("\n", $salida));
    }

    public function testVoterRenderizaPhpValidoConLosRoles(): void
    {
        $codigo = self::voter('App\\Security');

        self::assertPhpValido($codigo);
        self::assertStringContainsString("    public const ROLES = [\n        'ROLE_ADMIN',\n        'ROLE_REPORTE',\n    ];", $codigo);
        self::assertStringContainsString("const ACCEDER   = 'product_acceder';", $codigo);
        self::assertStringContainsString("const CREAR     = 'product_crear';", $codigo);
    }

    /**
     * Ejecuta el Voter generado: concede con cualquiera de los roles y se abstiene si el sujeto no corresponde.
     */
    public function testVoterGeneradoVotaSegunLosRoles(): void
    {
        $namespace = 'GALes\\MakerBundle\\Tests\\Generado'.uniqid();
        eval('namespace '.$namespace.'; class Product {}');
        $archivo = tempnam(sys_get_temp_dir(), 'voter');
        file_put_contents($archivo, self::voter($namespace));
        require $archivo;
        unlink($archivo);

        $voterClass = $namespace.'\\ProductVoter';
        $productClass = $namespace.'\\Product';
        $votar = function (array $roles, string $atributo, mixed $sujeto) use ($voterClass): int {
            $security = $this->createMock(Security::class);
            $security->method('isGranted')->willReturnCallback(fn ($rol) => in_array($rol, $roles, true));

            return (new $voterClass($security))->vote($this->createMock(TokenInterface::class), $sujeto, [$atributo]);
        };

        self::assertSame(VoterInterface::ACCESS_GRANTED, $votar(['ROLE_REPORTE'], $voterClass::ACCEDER, null));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $votar(['ROLE_ADMIN'], $voterClass::CREAR, null));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $votar(['ROLE_REPORTE'], $voterClass::EDITAR, new $productClass()));
        self::assertSame(VoterInterface::ACCESS_DENIED, $votar(['ROLE_USER'], $voterClass::ACCEDER, null));
        self::assertSame(VoterInterface::ACCESS_DENIED, $votar(['ROLE_USER'], $voterClass::ELIMINAR, new $productClass()));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $votar(['ROLE_ADMIN'], $voterClass::VER, null));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $votar(['ROLE_ADMIN'], $voterClass::ACCEDER, new $productClass()));
    }

    public function testTestDelVoterRenderizaPhpValido(): void
    {
        $codigo = self::render('security/VoterTest.tpl.php', [
            'namespace' => 'App\\Tests\\Security',
            'class_name' => 'ProductVoterTest',
            'entity_full_class_name' => 'App\\Entity\\Product',
            'entity_class_name' => 'Product',
            'voter_full_class_name' => 'App\\Security\\ProductVoter',
            'voter_class_name' => 'ProductVoter',
        ]);

        self::assertPhpValido($codigo);
        self::assertStringContainsString('ProductVoter::ROLES', $codigo);
        self::assertStringContainsString('ProductVoter::ACCEDER', $codigo);
    }

    public function testControladorExigeAccederEnLaClaseYCrearEnElAlta(): void
    {
        $codigo = self::render('crud/controller/Controller.tpl.php', [
            'namespace' => 'App\\Controller',
            'class_name' => 'ProductController',
            'entity_full_class_name' => 'App\\Entity\\Product',
            'entity_class_name' => 'Product',
            'form_full_class_name' => 'App\\Form\\ProductType',
            'filter_type' => 'input',
            'form_class_name' => 'ProductType',
            'crud_service_full_class_name' => 'App\\Service\\ProductCrudService',
            'voter_full_class_name' => 'App\\Security\\ProductVoter',
            'voter_class_name' => 'ProductVoter',
            'route_path' => '/product',
            'route_name' => 'product',
            'templates_path' => 'product',
            'entity_var_plural' => 'products',
            'entity_twig_var_plural' => 'products',
            'entity_var_singular' => 'product',
            'entity_twig_var_singular' => 'product',
            'entity_identifier' => 'id',
            'entity_identifier_numeric' => true,
        ]);

        self::assertPhpValido($codigo);
        self::assertStringContainsString('use Symfony\\Component\\Security\\Http\\Attribute\\IsGranted;', $codigo);
        self::assertMatchesRegularExpression('/#\[IsGranted\(ProductVoter::ACCEDER\)\]\s*class ProductController/', $codigo);
        self::assertMatchesRegularExpression('/public function new\(Request \$request\): Response\s*\{\s*\$this->denyAccessUnlessGranted\(ProductVoter::CREAR\);/', $codigo);
    }
}
