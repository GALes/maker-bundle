<?php

namespace GALes\MakerBundle\Tests\Maker;

use GALes\MakerBundle\Maker\MakeCrud;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;

/**
 * Las preguntas con autocompletado de MakeCrud::interact() solo se ejecutan si MakerCommand
 * no pregunta antes los argumentos con un ask() común.
 */
final class MakeCrudInteractivoTest extends TestCase
{
    public function testLosTresArgumentosSePreguntanEnInteract(): void
    {
        $maker = (new \ReflectionClass(MakeCrud::class))->newInstanceWithoutConstructor();
        $inputConfig = new InputConfiguration();

        $maker->configureCommand(new Command('gales:make:crud'), $inputConfig);

        foreach (['entity-class', 'filter-type', 'base-template'] as $argumento) {
            self::assertContains($argumento, $inputConfig->getNonInteractiveArguments());
        }
    }
}
