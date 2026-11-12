<?php

namespace GALes\MakerBundle\Tests\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use GALes\MakerBundle\Doctrine\EntityDetails;
use PHPUnit\Framework\TestCase;

/**
 * #34: el CRUD generado valida según la columna (NotBlank si no es nullable, Length según el length)
 * para que un nombre vacío o de 256 caracteres no termine en un 500 de la base.
 */
final class ValidacionColumnaTest extends TestCase
{
    private function campos(array $columnas): array
    {
        $metadata = new ClassMetadata(EntidadDePrueba::class);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        foreach ($columnas as $columna) {
            $metadata->mapField($columna);
        }
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

        return (new EntityDetails($metadata))->getFormFields();
    }

    public function testStringNoNullableLlevaNotBlankYLengthDeLaColumna(): void
    {
        $campos = $this->campos([['fieldName' => 'nombre', 'type' => 'string', 'length' => 100]]);

        self::assertSame(['new Assert\NotBlank()', 'new Assert\Length(max: 100)'], $campos['nombre']['constraints_code']);
    }

    public function testStringSinLengthUsa255(): void
    {
        $campos = $this->campos([['fieldName' => 'nombre', 'type' => 'string']]);

        self::assertContains('new Assert\Length(max: 255)', $campos['nombre']['constraints_code']);
    }

    public function testStringNullableSoloLlevaLength(): void
    {
        $campos = $this->campos([['fieldName' => 'nombre', 'type' => 'string', 'length' => 50, 'nullable' => true]]);

        self::assertSame(['new Assert\Length(max: 50)'], $campos['nombre']['constraints_code']);
    }

    public function testIdBooleanosYNullablesNoNumericosNoLlevanRestricciones(): void
    {
        $campos = $this->campos([
            ['fieldName' => 'activo', 'type' => 'boolean'],
            ['fieldName' => 'edad', 'type' => 'integer', 'nullable' => true],
        ]);

        self::assertArrayNotHasKey('constraints_code', $campos['id']);
        self::assertArrayNotHasKey('constraints_code', $campos['activo']);
        self::assertArrayNotHasKey('constraints_code', $campos['edad']);
    }

    public function testNumericoNoNullableLlevaNotBlank(): void
    {
        $campos = $this->campos([['fieldName' => 'edad', 'type' => 'integer']]);

        self::assertSame(['new Assert\NotBlank()'], $campos['edad']['constraints_code']);
    }

    public function testLaPlantillaDelFormularioEmiteLasRestricciones(): void
    {
        $campos = $this->campos([
            ['fieldName' => 'nombre', 'type' => 'string', 'length' => 100],
            ['fieldName' => 'activo', 'type' => 'boolean'],
        ]);
        $codigo = $this->renderizar($campos);

        self::assertStringContainsString('use Symfony\Component\Validator\Constraints as Assert;', $codigo);
        self::assertStringContainsString("->add('nombre', null, [", $codigo);
        self::assertStringContainsString("'constraints' => [", $codigo);
        self::assertStringContainsString('new Assert\NotBlank(),', $codigo);
        self::assertStringContainsString('new Assert\Length(max: 100),', $codigo);
        self::assertStringContainsString("->add('activo')", $codigo);
        self::assertNull(self::lint($codigo), 'El formulario generado debe ser PHP válido');
    }

    public function testLaPlantillaNoImportaAssertSinRestricciones(): void
    {
        $codigo = $this->renderizar($this->campos([['fieldName' => 'activo', 'type' => 'boolean']]));

        self::assertStringNotContainsString('Constraints as Assert', $codigo);
        self::assertNull(self::lint($codigo));
    }

    private function renderizar(array $campos): string
    {
        $variables = [
            'namespace' => 'App\Form',
            'class_name' => 'EntidadType',
            'bounded_full_class_name' => EntidadDePrueba::class,
            'bounded_class_name' => 'EntidadDePrueba',
            'entity_var_singular' => 'entidad',
            'form_fields' => array_map(static fn (array $c) => $c + ['type' => null, 'options_code' => null], $campos),
            'field_type_use_statements' => [],
            'join_type_use_statements' => [],
            'constraint_use_statements' => [],
        ];

        extract($variables);
        ob_start();
        include __DIR__.'/../../src/Resources/skeleton/form/Type.tpl.php';

        return ob_get_clean();
    }

    private static function lint(string $codigo): ?string
    {
        $archivo = tempnam(sys_get_temp_dir(), 'form');
        file_put_contents($archivo, $codigo);
        exec('php -l '.escapeshellarg($archivo).' 2>&1', $salida, $estado);
        unlink($archivo);

        return 0 === $estado ? null : implode("\n", $salida);
    }
}
