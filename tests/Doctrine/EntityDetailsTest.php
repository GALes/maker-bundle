<?php

namespace GALes\MakerBundle\Tests\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use GALes\MakerBundle\Doctrine\EntityDetails;
use PHPUnit\Framework\TestCase;

/**
 * #18: EntityDetails no debe escribir propiedades dinámicas sobre FieldMapping (deprecado desde PHP 8.2).
 */
final class EntityDetailsTest extends TestCase
{
    private function entityDetails(): EntityDetails
    {
        $metadata = new ClassMetadata(EntidadDePrueba::class);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->mapField(['fieldName' => 'nombre', 'type' => 'string']);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

        return new EntityDetails($metadata);
    }

    public function testGetFormFieldsNoEmiteDeprecaciones(): void
    {
        $deprecaciones = [];
        set_error_handler(static function (int $nivel, string $mensaje) use (&$deprecaciones): bool {
            $deprecaciones[] = $mensaje;

            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $detalles = $this->entityDetails();
            $campos = $detalles->getFormFields();
            $detalles->getSpiriitFormFields();
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $deprecaciones);
        self::assertSame(['id', 'nombre'], array_keys($campos));
    }

    public function testGetFormFieldsNoModificaLaMetadataDeDoctrine(): void
    {
        $metadata = new ClassMetadata(EntidadDePrueba::class);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);

        $antes = serialize($metadata->fieldMappings);
        (new EntityDetails($metadata))->getFormFields();

        self::assertSame($antes, serialize($metadata->fieldMappings));
    }
}

final class EntidadDePrueba
{
    public ?int $id = null;
    public ?string $nombre = null;
}
