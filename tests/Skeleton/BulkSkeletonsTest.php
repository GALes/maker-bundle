<?php

namespace GALes\MakerBundle\Tests\Skeleton;

use PHPUnit\Framework\TestCase;

/**
 * #22: el controlador y la vista generados usan el mapa BULK_ACTIONS, el ejecutor y el token CSRF.
 */
final class BulkSkeletonsTest extends TestCase
{
    private const BASE = __DIR__.'/../../src/Resources/skeleton/';

    private static function render(string $template, array $vars): string
    {
        extract($vars);
        ob_start();
        include self::BASE.$template;

        return (string) ob_get_clean();
    }

    private static function controller(): string
    {
        return self::render('crud/controller/Controller.tpl.php', [
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
    }

    public function testControladorEscribeLaRutaConPrefijo(): void
    {
        $codigo = self::render('crud/controller/Controller.tpl.php', [
            'namespace' => 'App\\Controller',
            'class_name' => 'SolicitudController',
            'entity_full_class_name' => 'App\\Entity\\Solicitud',
            'entity_class_name' => 'Solicitud',
            'form_full_class_name' => 'App\\Form\\SolicitudType',
            'filter_type' => 'input',
            'form_class_name' => 'SolicitudType',
            'crud_service_full_class_name' => 'App\\Service\\SolicitudCrudService',
            'voter_full_class_name' => 'App\\Security\\SolicitudVoter',
            'voter_class_name' => 'SolicitudVoter',
            'route_path' => '/admin/solicitud',
            'route_name' => 'solicitud',
            'templates_path' => 'solicitud',
            'entity_var_plural' => 'solicituds',
            'entity_twig_var_plural' => 'solicituds',
            'entity_var_singular' => 'solicitud',
            'entity_twig_var_singular' => 'solicitud',
            'entity_identifier' => 'id',
            'entity_identifier_numeric' => true,
        ]);

        self::assertStringContainsString('#[Route("/admin/solicitud")]', $codigo);
    }

    public function testControladorRenderizaPhpValido(): void
    {
        $codigo = self::controller();
        $archivo = tempnam(sys_get_temp_dir(), 'ctrl');
        file_put_contents($archivo, $codigo);
        exec('php -l '.escapeshellarg($archivo).' 2>&1', $salida, $codigo_salida);
        unlink($archivo);

        self::assertSame(0, $codigo_salida, implode("\n", $salida));
    }

    public function testControladorUsaMapaEjecutorYCsrf(): void
    {
        $codigo = self::controller();

        self::assertStringContainsString('private const BULK_ACTIONS = [', $codigo);
        self::assertStringContainsString("'delete' => ProductVoter::ELIMINAR,", $codigo);
        self::assertStringContainsString("private const BULK_CSRF_ID = 'bulk_product';", $codigo);
        self::assertStringContainsString('isCsrfTokenValid(self::BULK_CSRF_ID', $codigo);
        self::assertStringContainsString('BulkActionRunner $bulkRunner', $codigo);
        self::assertStringContainsString('$bulkRunner->run(Product::class, $action, $ids, self::BULK_ACTIONS)', $codigo);
        self::assertStringContainsString('index(Request $request, BulkActionRegistry $bulkActions)', $codigo);
        self::assertStringContainsString("'bulkActions' => \$bulkActions->describe(array_keys(self::BULK_ACTIONS), Product::class)", $codigo);
        self::assertStringContainsString('BadRequestHttpException', $codigo);
        self::assertStringNotContainsString('"delete")', $codigo);
        self::assertStringNotContainsString("'delete')", $codigo);
        self::assertStringNotContainsString('BulkDeleteService', $codigo);
    }

    public function testVistaIndexTieneCsrfYNingunaOpcionDeleteFija(): void
    {
        $helper = new class {
            public function asHumanWords(string $s): string
            {
                return $s;
            }

            public function getHeadPrintCode(string $title, string $base): string
            {
                return '';
            }

            public function getEntityFieldPrintCode(string $var, array $field): string
            {
                return $var.'.'.$field['fieldName'];
            }
        };

        $twig = self::render('crud/templates/index.tpl.php', [
            'custom_helper' => $helper,
            'template_base_twig' => 'base.html.twig',
            'entity_class_name' => 'Product',
            'entity_fields' => [['fieldName' => 'name']],
            'entity_identifier' => 'id',
            'entity_twig_var_plural' => 'products',
            'entity_twig_var_singular' => 'product',
            'route_name' => 'product',
            'filter_type' => 'none',
        ]);

        self::assertStringContainsString("csrf_token('bulk_product')", $twig);
        self::assertStringContainsString('name="_token"', $twig);
        self::assertStringContainsString('{% if bulkActions is not empty %}', $twig);
        self::assertStringContainsString('{% for bulkAction in bulkActions %}', $twig);
        self::assertStringContainsString('name="bulk_action" form="bulk_form"', $twig);
        self::assertStringNotContainsString('<option value="delete"', $twig);
    }

    public function testNoQuedanSkeletonsDelServicioDeBorrado(): void
    {
        self::assertFileDoesNotExist(self::BASE.'service/BulkDeleteService.tpl.php');
        self::assertFileDoesNotExist(self::BASE.'service/BulkDeleteServiceTest.tpl.php');
    }
}
