<?php

namespace GALes\MakerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GALesMakerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//        dd($configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('gales.maker.gales_maker');
        $definition->setArgument(0, $config['nombre']);
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'gales_maker';
    }


}