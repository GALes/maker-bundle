<?php

namespace GALes\MakerBundle\Tests\Skeleton;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * #20: con filter-type none, el CrudService, el controlador y el listado generados no referencian filtros inexistentes.
 */
final class FilterTypeNoneSkeletonsTest extends TestCase
{
    private const SKELETON = __DIR__.'/../../src/Resources/skeleton/';

    private function render(string $plantilla, array $variables): string
    {
        extract($variables);
        ob_start();
        include self::SKELETON.$plantilla;

        return (string) ob_get_clean();
    }

    private function variablesServicio(string $filterType): array
    {
        return [
            'namespace' => 'App\\Service', 'class_name' => 'SolicitudCrudService',
            'entity_full_class_name' => 'App\\Entity\\Solicitud', 'entity_class_name' => 'Solicitud',
            'form_full_class_name' => 'App\\Form\\SolicitudType', 'form_class_name' => 'SolicitudType',
            'form_filter_full_class_name' => $filterType === 'none' ? null : 'App\\Form\\SolicitudFilterType',
            'form_filter_class_name' => $filterType === 'none' ? null : 'SolicitudFilterType',
            'filter_type' => $filterType, 'entity_fields' => [], 'entity_var_plural' => 'solicitudes',
            'entity_var_singular' => 'solicitud', 'entity_identifier' => 'id', 'route_name' => 'solicitud',
            'custom_helper' => null,
        ];
    }

    public function testElCrudServiceConNoneNoReferenciaFiltros(): void
    {
        $codigo = $this->render('service/CrudService.tpl.php', $this->variablesServicio('none'));

        self::assertStringNotContainsString('FilterType', $codigo);
        self::assertStringNotContainsString('function filter(', $codigo);
        self::assertStringNotContainsString('MultiSearch', $codigo);
        self::assertStringNotContainsString('FilterBuilderUpdater', $codigo);
        self::assertStringContainsString('use App\\Form\\SolicitudType;', $codigo);
        self::assertMatchesRegularExpression('/\$urlGenerator\s*\)\s*\{/', $codigo);
        self::assertSame(0, $this->lint($codigo));
    }

    #[DataProvider('conFiltro')]
    public function testElCrudServiceConFiltroSigueGenerandoElFiltro(string $tipo): void
    {
        $codigo = $this->render('service/CrudService.tpl.php', $this->variablesServicio($tipo));

        self::assertStringContainsString('function filter(', $codigo);
        self::assertStringContainsString('use App\\Form\\SolicitudFilterType;', $codigo);
        self::assertSame(0, $this->lint($codigo));
    }

    public static function conFiltro(): iterable
    {
        yield 'input' => ['input'];
        yield 'form' => ['form'];
    }

    public function testElListadoConNoneNoUsaFilterForm(): void
    {
        $plantilla = file_get_contents(self::SKELETON.'crud/templates/index.tpl.php');

        self::assertMatchesRegularExpression("/<\\?php if \\( \\\$filter_type !== 'none' \\): \\?>\\s*\\{%- if form_errors\\(filterForm\\)/", $plantilla);
    }

    public function testElControladorConNoneNoLlamaAFilter(): void
    {
        $plantilla = file_get_contents(self::SKELETON.'crud/controller/actions/index.tpl.php');

        self::assertMatchesRegularExpression("/<\\?php if \\(\\\$filter_type !== 'none'\\): \\?>\\s*\\\$filter_response = .*filter\\(/", $plantilla);
        self::assertMatchesRegularExpression("/<\\?php if \\(\\\$filter_type !== 'none'\\): \\?>\\s*'filterForm' =>/", $plantilla);
    }

    public static function todosLosTipos(): iterable
    {
        yield 'none' => ['none'];
        yield 'input' => ['input'];
        yield 'form' => ['form'];
    }

    #[DataProvider('todosLosTipos')]
    public function testElControladorSeRenderizaSinVariablesIndefinidas(string $tipo): void
    {
        $avisos = [];
        set_error_handler(static function (int $nivel, string $mensaje) use (&$avisos): bool {
            $avisos[] = $mensaje;

            return true;
        });

        try {
            $codigo = $this->render('crud/controller/Controller.tpl.php', [
                'namespace' => 'App\\Controller', 'class_name' => 'SolicitudController',
                'entity_full_class_name' => 'App\\Entity\\Solicitud', 'entity_class_name' => 'Solicitud',
                'form_full_class_name' => 'App\\Form\\SolicitudType', 'form_class_name' => 'SolicitudType',
                'filter_type' => $tipo,
                'crud_service_full_class_name' => 'App\\Service\\SolicitudCrudService',
                'voter_full_class_name' => 'App\\Security\\SolicitudVoter', 'voter_class_name' => 'SolicitudVoter',
                'route_path' => '/solicitud', 'route_name' => 'solicitud', 'templates_path' => 'solicitud',
                'entity_var_plural' => 'solicitudes', 'entity_twig_var_plural' => 'solicitudes',
                'entity_var_singular' => 'solicitud', 'entity_twig_var_singular' => 'solicitud',
                'entity_identifier' => 'id', 'entity_identifier_numeric' => true,
            ]);
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $avisos);
        self::assertSame(0, $this->lint($codigo));
        self::assertSame($tipo !== 'none', str_contains($codigo, '->filter($queryBuilder, $request)'));
        self::assertSame($tipo !== 'none', str_contains($codigo, "'filterForm' =>"));
    }

    private function lint(string $codigo): int
    {
        $archivo = tempnam(sys_get_temp_dir(), 'lint').'.php';
        file_put_contents($archivo, $codigo);
        exec('php -l '.escapeshellarg($archivo).' 2>&1', $salida, $codigoSalida);
        unlink($archivo);

        return $codigoSalida;
    }
}
