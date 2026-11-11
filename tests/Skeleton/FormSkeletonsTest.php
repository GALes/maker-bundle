<?php

namespace GALes\MakerBundle\Tests\Skeleton;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * #18: las plantillas de formulario declaran el tipo de retorno void en buildForm() y configureOptions().
 */
final class FormSkeletonsTest extends TestCase
{
    public static function plantillas(): iterable
    {
        foreach (['Type', 'FilterType', 'FullFilterType'] as $nombre) {
            yield $nombre => [__DIR__.'/../../src/Resources/skeleton/form/'.$nombre.'.tpl.php'];
        }
    }

    #[DataProvider('plantillas')]
    public function testBuildFormYConfigureOptionsDeclaranVoid(string $plantilla): void
    {
        $codigo = file_get_contents($plantilla);

        self::assertMatchesRegularExpression('/function buildForm\(FormBuilderInterface \$builder, array \$options\): void\b/', $codigo);
        self::assertMatchesRegularExpression('/function configureOptions\(OptionsResolver \$resolver\): void\b/', $codigo);
    }
}
