<?php

namespace GALes\MakerBundle\Tests\Maker;

use GALes\MakerBundle\Maker\MakeCrud;
use GALes\MakerBundle\Maker\Roles\AvailableRolesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

/**
 * #9: --route-prefix (o gales_maker.crud.route_prefix) antepone un prefijo a la ruta del CRUD.
 */
final class MakeCrudRoutePrefixTest extends TestCase
{
    private function maker(string $prefijoPorDefecto = ''): MakeCrud
    {
        $maker = (new \ReflectionClass(MakeCrud::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($maker, 'defaultRoutePrefix'))->setValue($maker, $prefijoPorDefecto);
        (new \ReflectionProperty($maker, 'rolesProvider'))->setValue($maker, new AvailableRolesProvider([]));

        return $maker;
    }

    private function command(MakeCrud $maker): Command
    {
        $command = new Command('gales:make:crud');
        $maker->configureCommand($command, new InputConfiguration());

        return $command;
    }

    private function input(Command $command, array $parametros = []): InputInterface
    {
        return new ArrayInput($parametros + [
            'entity-class'  => 'Solicitud',
            'filter-type'   => 'input',
            'base-template' => 'base.html.twig',
        ], $command->getDefinition());
    }

    private function resolver(MakeCrud $maker, InputInterface $input): string
    {
        return (new \ReflectionMethod($maker, 'resolveRoutePrefix'))->invoke($maker, $input);
    }

    public function testLaOpcionExisteYRequiereValor(): void
    {
        $opcion = $this->command($this->maker())->getDefinition()->getOption('route-prefix');

        self::assertTrue($opcion->isValueRequired());
        self::assertFalse($opcion->isArray());
        self::assertNull($opcion->getDefault());
    }

    public function testSinOpcionNiConfiguracionNoHayPrefijo(): void
    {
        $maker = $this->maker();

        self::assertSame('', $this->resolver($maker, $this->input($this->command($maker))));
    }

    public function testLaOpcionDefinePrefijo(): void
    {
        $maker = $this->maker();
        $input = $this->input($this->command($maker), ['--route-prefix' => 'admin/']);

        self::assertSame('/admin', $this->resolver($maker, $input));
    }

    public function testSinOpcionUsaElPrefijoConfigurado(): void
    {
        $maker = $this->maker('/admin');

        self::assertSame('/admin', $this->resolver($maker, $this->input($this->command($maker))));
    }

    public function testLaOpcionPisaElPrefijoConfigurado(): void
    {
        $maker = $this->maker('/admin');
        $input = $this->input($this->command($maker), ['--route-prefix' => '/backoffice']);

        self::assertSame('/backoffice', $this->resolver($maker, $input));
    }

    public function testLaOpcionVaciaForzaSinPrefijo(): void
    {
        $maker = $this->maker('/admin');
        $input = $this->input($this->command($maker), ['--route-prefix' => '']);

        self::assertSame('', $this->resolver($maker, $input));
    }

    public function testPrefijoInvalidoCortaAntesDeGenerar(): void
    {
        $maker = $this->maker();
        $input = $this->input($this->command($maker), ['--route-prefix' => '/ad"min']);

        $this->expectException(\InvalidArgumentException::class);

        $this->resolver($maker, $input);
    }

    public function testGenerateValidaElPrefijoAntesDeTocarArchivos(): void
    {
        $maker = $this->maker();
        $input = $this->input($this->command($maker), ['--route-prefix' => '/ad"min']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no es un prefijo de ruta válido');

        // Sin ConsoleStyle ni Generator: si fallara después de validar, el error sería otro.
        $maker->generate($input, (new \ReflectionClass(ConsoleStyle::class))->newInstanceWithoutConstructor(), (new \ReflectionClass(Generator::class))->newInstanceWithoutConstructor());
    }
}
