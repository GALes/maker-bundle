<?php

namespace GALes\MakerBundle\Tests\Skeleton;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * #32: el filtro guardado en sesión se restituye al ordenar o paginar con una URL sin los parámetros del filtro.
 * Se ejecuta el filter() del CrudService generado con una petición real (formularios y sesión de Symfony).
 */
final class CrudServiceFiltroPersistenteTest extends TestCase
{
    private const SKELETON = __DIR__.'/../../src/Resources/skeleton/service/CrudService.tpl.php';
    private static int $contador = 0;

    public static function tipos(): iterable
    {
        yield 'form' => ['form', FiltroPersistenteFixtureType::class, 'filtro_persistente_fixture'];
        yield 'input' => ['input', FiltroPersistenteInputFixtureType::class, 'filtro_persistente_input_fixture'];
    }

    /** Devuelve [servicio, aplicaciones del filtro (por referencia), nombre del formulario]. */
    private function servicio(string $tipo, string $filtroClase): array
    {
        $claseServicio = 'FiltroPersistenteServicio'.(++self::$contador);
        $variables = [
            'namespace' => 'GALes\\MakerBundle\\Tests\\Skeleton\\Generado', 'class_name' => $claseServicio,
            'entity_full_class_name' => \stdClass::class, 'entity_class_name' => 'stdClass',
            'form_full_class_name' => \stdClass::class, 'form_class_name' => 'stdClass',
            'form_filter_full_class_name' => $filtroClase, 'form_filter_class_name' => '\\'.$filtroClase,
            'filter_type' => $tipo, 'entity_fields' => [], 'entity_var_plural' => 'xs', 'entity_var_singular' => 'x',
            'entity_identifier' => 'id', 'route_name' => 'x', 'custom_helper' => null,
        ];
        extract($variables);
        ob_start();
        include self::SKELETON;
        $codigo = (string) ob_get_clean();
        // Los `use` de las clases del fixture y de stdClass chocarían con los nombres ya importados en el eval.
        $codigo = preg_replace('/^use (stdClass|'.preg_quote($filtroClase, '/').');$/m', '', $codigo);
        eval(substr($codigo, strlen('<?php')));

        $clase = new \ReflectionClass('GALes\\MakerBundle\\Tests\\Skeleton\\Generado\\'.$claseServicio);
        $servicio = $clase->newInstanceWithoutConstructor();
        $aplicaciones = new \ArrayObject();
        $actualizador = new class($aplicaciones) {
            public function __construct(private \ArrayObject $aplicaciones) {}
            public function addFilterConditions($form, $qb): void { $this->aplicaciones[] = $form->getData(); }
            public function searchForm($qb, $form): void { $this->aplicaciones[] = ['search' => $form->getData()]; }
        };
        $generador = new class implements UrlGeneratorInterface {
            public function setContext(RequestContext $context): void {}
            public function getContext(): RequestContext { return new RequestContext(); }
            public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
            {
                return '/x?'.http_build_query($parameters);
            }
        };
        $formFactory = Forms::createFormFactoryBuilder()->addExtension(new HttpFoundationExtension())->getFormFactory();
        $propiedades = [
            'formFactory' => $formFactory, 'urlGenerator' => $generador,
            $tipo === 'form' ? 'spiriitQueryBuilderUpdater' : 'multiSearchBuilderService' => $actualizador,
        ];
        foreach ($propiedades as $nombre => $valor) {
            $clase->getProperty($nombre)->setValue($servicio, $valor);
        }

        return [$servicio, $aplicaciones, $formFactory->create($filtroClase)->getName()];
    }

    private function peticion(Session $sesion, array $query): Request
    {
        $peticion = Request::create('/x', 'GET', $query);
        $peticion->setSession($sesion);
        $peticion->attributes->set('_route', 'x_index');

        return $peticion;
    }

    private function filtrar(object $servicio, Request $peticion): array|RedirectResponse
    {
        $qb = new class extends QueryBuilder { public function __construct() {} };

        return $servicio->filter($qb, $peticion);
    }

    #[DataProvider('tipos')]
    public function testOrdenarSinParametrosDelFiltroRestituyeElFiltroGuardado(string $tipo, string $filtro, string $nombre): void
    {
        [$servicio, $aplicaciones] = $this->servicio($tipo, $filtro);
        $sesion = new Session(new MockArraySessionStorage());
        $campo = $tipo === 'form' ? 'nombre' : 'search';

        $this->filtrar($servicio, $this->peticion($sesion, [$nombre => [$campo => 'lote 03'], 'filter_action' => 'filter']));
        self::assertCount(1, $aplicaciones);

        $peticion = $this->peticion($sesion, ['pcg_sort_col' => 'nombre', 'pcg_sort_order' => 'asc']);
        $this->filtrar($servicio, $peticion);

        self::assertCount(2, $aplicaciones, 'El filtro guardado se aplica al ordenar.');
        self::assertSame('lote 03', $aplicaciones[1][$campo]);
        self::assertSame('nombre', $peticion->query->get('pcg_sort_col'));
    }

    #[DataProvider('tipos')]
    public function testPaginarYCambiarTamanioSinParametrosDelFiltroRestituyeElFiltro(string $tipo, string $filtro, string $nombre): void
    {
        [$servicio, $aplicaciones] = $this->servicio($tipo, $filtro);
        $sesion = new Session(new MockArraySessionStorage());
        $campo = $tipo === 'form' ? 'nombre' : 'search';

        $this->filtrar($servicio, $this->peticion($sesion, [$nombre => [$campo => 'a'], 'filter_action' => 'filter']));
        $this->filtrar($servicio, $this->peticion($sesion, ['pcg_show' => '20', 'pcg_page' => '2']));
        // El filtro sigue guardado para la siguiente navegación, que conserva además el orden.
        $this->filtrar($servicio, $this->peticion($sesion, ['pcg_sort_col' => 'id']));

        self::assertCount(3, $aplicaciones);
        self::assertSame('a', $aplicaciones[2][$campo]);
    }

    #[DataProvider('tipos')]
    public function testSinFiltroGuardadoOrdenarNoAplicaNingunFiltro(string $tipo, string $filtro): void
    {
        [$servicio, $aplicaciones] = $this->servicio($tipo, $filtro);

        $this->filtrar($servicio, $this->peticion(new Session(new MockArraySessionStorage()), ['pcg_sort_col' => 'nombre']));

        self::assertCount(0, $aplicaciones);
    }

    #[DataProvider('tipos')]
    public function testLimpiarFiltrosOlvidaElFiltroYUnOrdenPosteriorNoLoRestituye(string $tipo, string $filtro, string $nombre): void
    {
        [$servicio, $aplicaciones] = $this->servicio($tipo, $filtro);
        $sesion = new Session(new MockArraySessionStorage());
        $campo = $tipo === 'form' ? 'nombre' : 'search';

        $this->filtrar($servicio, $this->peticion($sesion, [$nombre => [$campo => 'a'], 'filter_action' => 'filter']));
        // "Limpiar Filtros" envía el formulario con sus valores y filter_action=reset.
        $reset = $this->peticion($sesion, [$nombre => [$campo => 'a'], 'filter_action' => 'reset']);
        $this->filtrar($servicio, $reset);
        self::assertFalse($reset->query->has($nombre));

        // Un orden desde la página del reset (los enlaces mezclan la query actual) y otro sin parámetros del filtro.
        $this->filtrar($servicio, $this->peticion($sesion, ['pcg_sort_col' => 'nombre']));
        $this->filtrar($servicio, $this->peticion($sesion, ['pcg_sort_col' => 'id']));

        self::assertCount(1, $aplicaciones, 'Tras limpiar no se vuelve a aplicar el filtro.');
    }

    #[DataProvider('tipos')]
    public function testUnFiltroNuevoReemplazaAlGuardado(string $tipo, string $filtro, string $nombre): void
    {
        [$servicio, $aplicaciones] = $this->servicio($tipo, $filtro);
        $sesion = new Session(new MockArraySessionStorage());
        $campo = $tipo === 'form' ? 'nombre' : 'search';

        $this->filtrar($servicio, $this->peticion($sesion, [$nombre => [$campo => 'a'], 'filter_action' => 'filter']));
        $this->filtrar($servicio, $this->peticion($sesion, [$nombre => [$campo => 'b'], 'filter_action' => 'filter']));
        $this->filtrar($servicio, $this->peticion($sesion, ['pcg_page' => '2']));

        self::assertSame('b', $aplicaciones[2][$campo]);
    }
}

class FiltroPersistenteFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('nombre', TextType::class)->setMethod('GET');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['allow_extra_fields' => true, 'csrf_protection' => false, 'validation_groups' => ['filtering']]);
    }
}

class FiltroPersistenteInputFixtureType extends FiltroPersistenteFixtureType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', TextType::class)->setMethod('GET');
    }
}
