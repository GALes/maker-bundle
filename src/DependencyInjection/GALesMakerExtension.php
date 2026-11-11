<?php

namespace GALes\MakerBundle\DependencyInjection;

use GALes\MakerBundle\Bulk\BulkActionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class GALesMakerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        // Toda clase que implemente BulkActionInterface se registra sola como acción masiva.
        $container->registerForAutoconfiguration(BulkActionInterface::class)->addTag('gales_maker.bulk_action');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('gales.maker.gales_maker');
        $definition->setArgument(0, $config['nombre']);

        $container->getDefinition('gales.maker.crud_roles_provider')
            ->setArgument(1, $config['crud']['roles_source'])
            ->setArgument(2, $config['crud']['default_roles']);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'gales_maker';
    }

}