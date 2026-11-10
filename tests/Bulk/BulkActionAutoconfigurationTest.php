<?php

namespace GALes\MakerBundle\Tests\Bulk;

use GALes\MakerBundle\DependencyInjection\GALesMakerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * #22 (QA H1): una acción propia del proyecto debe recibir el tag sin configurarlo a mano.
 */
final class BulkActionAutoconfigurationTest extends TestCase
{
    public function testUnaAccionAutoconfiguradaRecibeElTag(): void
    {
        $container = new ContainerBuilder();
        (new GALesMakerExtension())->load([], $container);
        $container->register(FakeBulkAction::class, FakeBulkAction::class)->setAutoconfigured(true);

        (new ResolveInstanceofConditionalsPass())->process($container);

        self::assertArrayHasKey('gales_maker.bulk_action', $container->getDefinition(FakeBulkAction::class)->getTags());
    }
}
