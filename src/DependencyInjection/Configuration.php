<?php

namespace GALes\MakerBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('gales_maker');
        $rootNode = $treeBuilder->getRootNode('gales_maker');

        $rootNode
            ->children()
                ->node('nombre', 'variable')->defaultValue('Mundo')->info('parametro de prueba')->end()
            ->end()
        ;

        return $treeBuilder;
    }

}