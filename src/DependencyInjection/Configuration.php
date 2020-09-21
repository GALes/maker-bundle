<?php

namespace GALes\MakerBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('gales_maker');

        $rootNode
            ->children()
                ->node('nombre', 'variable')->defaultValue('Mundo')->info('parametro de prueba')->end()
            ->end()
        ;

        return $treeBuilder;
    }

}