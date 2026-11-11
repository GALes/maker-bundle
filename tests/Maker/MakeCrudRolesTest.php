<?php

namespace GALes\MakerBundle\Tests\Maker;

use GALes\MakerBundle\Maker\MakeCrud;
use GALes\MakerBundle\Maker\Roles\AvailableRolesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * #25: el CRUD generado es sólo para los roles fijos (ROLE_ADMIN) salvo que se elijan otros, con --roles o con un paso interactivo.
 */
final class MakeCrudRolesTest extends TestCase
{
    private const JERARQUIA = [
        'ROLE_ADMIN'        => ['ROLE_ADMIN', 'ROLE_FILEDOWNLOAD', 'ROLE_REPORTE'],
        'ROLE_FILEDOWNLOAD' => ['ROLE_FILEDOWNLOAD'],
    ];

    private BufferedOutput $salida;

    private ?string $raiz = null;

    protected function setUp(): void
    {
        // Sin terminal real: el autocompletado se desactiva y la respuesta se lee como texto plano.
        QuestionHelper::disableStty();
    }

    protected function tearDown(): void
    {
        if (null !== $this->raiz) {
            (new Filesystem())->remove($this->raiz);
        }
    }

    private function maker(): MakeCrud
    {
        $maker = (new \ReflectionClass(MakeCrud::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($maker, 'rolesProvider'))->setValue($maker, new AvailableRolesProvider(self::JERARQUIA));

        return $maker;
    }

    private function command(MakeCrud $maker): Command
    {
        $command = new Command('gales:make:crud');
        $maker->configureCommand($command, new InputConfiguration());

        return $command;
    }

    /**
     * @param array<string, mixed> $parametros
     * @param string|null          $respuestas null: modo no interactivo; si no, las líneas que teclea el usuario
     */
    private function input(Command $command, array $parametros, ?string $respuestas): InputInterface
    {
        $input = new ArrayInput($parametros + [
            'entity-class'  => 'QaPrueba',
            'filter-type'   => 'input',
            'base-template' => 'base.html.twig',
        ], $command->getDefinition());
        $input->setInteractive(null !== $respuestas);

        if (null !== $respuestas) {
            $flujo = fopen('php://memory', 'r+');
            fwrite($flujo, $respuestas);
            rewind($flujo);
            $input->setStream($flujo);
        }

        return $input;
    }

    private function io(InputInterface $input): ConsoleStyle
    {
        $this->salida = new BufferedOutput();

        return new ConsoleStyle($input, $this->salida);
    }

    private function resolver(MakeCrud $maker, InputInterface $input): array
    {
        return (new \ReflectionMethod($maker, 'resolveRoles'))->invoke($maker, $input);
    }

    public function testLaOpcionRolesAceptaVariosValores(): void
    {
        $opcion = $this->command($this->maker())->getDefinition()->getOption('roles');

        self::assertTrue($opcion->isArray());
        self::assertTrue($opcion->isValueRequired());
    }

    public function testSinRolesElegidosSoloAccedeRoleAdmin(): void
    {
        $maker = $this->maker();

        self::assertSame(['ROLE_ADMIN'], $this->resolver($maker, $this->input($this->command($maker), [], null)));
    }

    public function testRolesPorOpcionSeSumanARoleAdmin(): void
    {
        $maker = $this->maker();
        $input = $this->input($this->command($maker), ['--roles' => ['role_reporte,ROLE_CONSULTA', 'ROLE_ADMIN']], null);

        self::assertSame(['ROLE_ADMIN', 'ROLE_REPORTE', 'ROLE_CONSULTA'], $this->resolver($maker, $input));
    }

    public function testRolInvalidoPorOpcionCortaAntesDeGenerar(): void
    {
        $maker = $this->maker();
        $input = $this->input($this->command($maker), ['--roles' => ['REPORTE']], null);

        $this->expectException(\InvalidArgumentException::class);

        $this->resolver($maker, $input);
    }

    public function testElPasoInteractivoPreguntaHastaEnterVacio(): void
    {
        $maker = $this->maker();
        $command = $this->command($maker);
        $input = $this->input($command, [], "role_reporte\nREPORTE\nROLE_NUEVO\n\n");

        $maker->interact($input, $this->io($input), $command);

        self::assertSame(['ROLE_REPORTE', 'ROLE_NUEVO'], $input->getOption('roles'));
        $salida = preg_replace('/\s+/', ' ', $this->salida->fetch());
        self::assertStringContainsString('Siempre tienen acceso: ROLE_ADMIN', $salida);
        self::assertStringContainsString('Roles definidos en el proyecto: ROLE_FILEDOWNLOAD, ROLE_REPORTE', $salida);
        self::assertStringContainsString('"REPORTE" no es un rol válido', $salida);
        self::assertStringContainsString('ROLE_NUEVO no está definido en el proyecto', $salida);
        self::assertSame(['ROLE_ADMIN', 'ROLE_REPORTE', 'ROLE_NUEVO'], $this->resolver($maker, $input));
    }

    public function testEnterVacioDejaSoloRoleAdmin(): void
    {
        $maker = $this->maker();
        $command = $this->command($maker);
        $input = $this->input($command, [], "\n");

        $maker->interact($input, $this->io($input), $command);

        self::assertSame(['ROLE_ADMIN'], $this->resolver($maker, $input));
    }

    public function testConLaOpcionNoPregunta(): void
    {
        $maker = $this->maker();
        $command = $this->command($maker);
        $input = $this->input($command, ['--roles' => ['ROLE_REPORTE']], '');

        $maker->interact($input, $this->io($input), $command);

        self::assertStringNotContainsString('Rol adicional', $this->salida->fetch());
        self::assertSame(['ROLE_ADMIN', 'ROLE_REPORTE'], $this->resolver($maker, $input));
    }

    private function generatorEn(string $raiz): Generator
    {
        return new class($raiz) extends Generator {
            public function __construct(private string $raiz)
            {
            }

            public function getRootDirectory(): string
            {
                return $this->raiz;
            }
        };
    }

    private function voterExistente(string $contenido): Generator
    {
        $this->raiz = sys_get_temp_dir().'/gales-maker-roles-'.uniqid();
        mkdir($this->raiz.'/src/Security', 0777, true);
        file_put_contents($this->raiz.'/src/Security/QaPruebaVoter.php', $contenido);

        return $this->generatorEn($this->raiz);
    }

    public function testVoterConservadoSinAccederNiCrearCortaLaGeneracion(): void
    {
        $generator = $this->voterExistente("<?php\nclass QaPruebaVoter {\n    const VER = 'qa_prueba_ver';\n}\n");

        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage('no define ACCEDER y CREAR');

        (new \ReflectionMethod(MakeCrud::class, 'assertVoterSupportsAccessAttributes'))
            ->invoke($this->maker(), $generator, 'src/Security/QaPruebaVoter.php', 'QaPruebaVoter');
    }

    public function testVoterConservadoConAccederYCrearSigue(): void
    {
        $generator = $this->voterExistente("<?php\nclass QaPruebaVoter {\n    const ACCEDER = 'a';\n    public const CREAR = 'c';\n}\n");

        (new \ReflectionMethod(MakeCrud::class, 'assertVoterSupportsAccessAttributes'))
            ->invoke($this->maker(), $generator, 'src/Security/QaPruebaVoter.php', 'QaPruebaVoter');

        $this->addToAssertionCount(1);
    }
}
