<?php

namespace GALes\MakerBundle\Tests\Maker;

use GALes\MakerBundle\Maker\MakeCrud;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * #17: repetir gales:make:crud no aborta por archivos existentes: pregunta por cada uno (por defecto omite) o,
 * con --force, los reemplaza.
 */
final class MakeCrudExistentesTest extends TestCase
{
    private const EXISTENTE = 'src/Form/QaPruebaType.php';

    private string $raiz;

    private BufferedOutput $salida;

    protected function setUp(): void
    {
        $this->raiz = sys_get_temp_dir().'/gales-maker-'.uniqid();
        mkdir($this->raiz.'/src/Form', 0777, true);
        file_put_contents($this->raiz.'/'.self::EXISTENTE, 'logica previa');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->raiz);
    }

    private function maker(bool $force = false): MakeCrud
    {
        $maker = (new \ReflectionClass(MakeCrud::class))->newInstanceWithoutConstructor();
        $this->propiedad($maker, 'filesystem', new Filesystem());
        $this->propiedad($maker, 'force', $force);

        return $maker;
    }

    private function propiedad(MakeCrud $maker, string $nombre, mixed $valor): void
    {
        $propiedad = new \ReflectionProperty($maker, $nombre);
        $propiedad->setValue($maker, $valor);
    }

    private function leer(MakeCrud $maker, string $nombre): mixed
    {
        return (new \ReflectionProperty($maker, $nombre))->getValue($maker);
    }

    private function invocar(MakeCrud $maker, string $metodo, mixed ...$args): mixed
    {
        return (new \ReflectionMethod($maker, $metodo))->invoke($maker, ...$args);
    }

    private function generator(): Generator
    {
        $raiz = $this->raiz;

        return new class($raiz) extends Generator {
            public function __construct(private string $raiz)
            {
            }

            public function getRootDirectory(): string
            {
                return $this->raiz;
            }

            public function getRootNamespace(): string
            {
                return 'App';
            }
        };
    }

    /**
     * @param string|null $respuesta null: modo no interactivo; si no, lo que el usuario teclea
     */
    private function io(?string $respuesta): ConsoleStyle
    {
        $input = new ArrayInput([]);
        $input->setInteractive(null !== $respuesta);

        if (null !== $respuesta) {
            $flujo = fopen('php://memory', 'r+');
            fwrite($flujo, $respuesta."\n");
            rewind($flujo);
            $input->setStream($flujo);
        }

        $this->salida = new BufferedOutput();

        return new ConsoleStyle($input, $this->salida);
    }

    public function testClassTargetPathSigueLaConvencionPsr4(): void
    {
        self::assertSame(
            self::EXISTENTE,
            $this->invocar($this->maker(), 'classTargetPath', $this->generator(), 'App\\Form\\QaPruebaType')
        );
    }

    public function testUnArchivoInexistenteSeGeneraSinPreguntar(): void
    {
        $omitir = $this->invocar($this->maker(), 'skipIfExists', $this->generator(), $this->io(null), 'src/Form/QaPruebaFilterType.php');

        self::assertFalse($omitir);
        self::assertSame('', $this->salida->fetch());
    }

    public function testSinInteraccionOmiteElArchivoExistenteYAvisa(): void
    {
        $maker = $this->maker();

        self::assertTrue($this->invocar($maker, 'skipIfExists', $this->generator(), $this->io(null), self::EXISTENTE));

        self::assertSame('logica previa', file_get_contents($this->raiz.'/'.self::EXISTENTE));
        self::assertSame([self::EXISTENTE], $this->leer($maker, 'omittedFiles'));
        self::assertStringContainsString('Omitido: src/Form/QaPruebaType.php ya existe y no se sobrescribe.', $this->salida->fetch());
    }

    public function testRespuestaNoOmiteElArchivo(): void
    {
        $maker = $this->maker();

        self::assertTrue($this->invocar($maker, 'skipIfExists', $this->generator(), $this->io('no'), self::EXISTENTE));

        self::assertFileExists($this->raiz.'/'.self::EXISTENTE);
        $salida = $this->salida->fetch();
        // el bloque de advertencia parte las líneas largas: se compara sin saltos ni espacios repetidos
        self::assertStringContainsString('[WARNING] src/Form/QaPruebaType.php ya existe.', $salida);
        self::assertStringContainsString('Si lo reemplazás se pierde', preg_replace('/\s+/', ' ', $salida));
        self::assertStringContainsString('¿Reemplazarlo?', $salida);
    }

    public function testRespuestaSiLiberaLaRutaParaReemplazar(): void
    {
        $maker = $this->maker();

        self::assertFalse($this->invocar($maker, 'skipIfExists', $this->generator(), $this->io('yes'), self::EXISTENTE));

        self::assertFileDoesNotExist($this->raiz.'/'.self::EXISTENTE);
        self::assertSame([self::EXISTENTE => 'logica previa'], $this->leer($maker, 'replacedFiles'));
    }

    public function testForceReemplazaSinPreguntar(): void
    {
        $maker = $this->maker(true);

        self::assertFalse($this->invocar($maker, 'skipIfExists', $this->generator(), $this->io(null), self::EXISTENTE));

        self::assertFileDoesNotExist($this->raiz.'/'.self::EXISTENTE);
        self::assertStringNotContainsString('¿Reemplazarlo?', $this->salida->fetch());
    }

    public function testSiLaGeneracionFallaSeRestauranLosArchivosReemplazados(): void
    {
        $maker = $this->maker(true);
        $generator = $this->generator();
        $this->invocar($maker, 'skipIfExists', $generator, $this->io(null), self::EXISTENTE);

        $this->invocar($maker, 'restoreReplacedFiles', $generator);

        self::assertSame('logica previa', file_get_contents($this->raiz.'/'.self::EXISTENTE));
        self::assertSame([], $this->leer($maker, 'replacedFiles'));
    }
}
