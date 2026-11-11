<?php

namespace GALes\MakerBundle\DependencyInjection;

use GALes\MakerBundle\Maker\Roles\RoleNormalizer;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gales_maker');
        $rootNode = $treeBuilder->getRootNode('gales_maker');

        $rootNode
            ->children()
                ->node('nombre', 'variable')->defaultValue('Mundo')->info('parametro de prueba')->end()
                ->arrayNode('crud')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('roles_source')
                            ->defaultNull()
                            ->info('Método estático o constante ("Clase::metodo" o "Clase::CONSTANTE") que devuelve los roles que gales:make:crud sugiere al elegir quién accede al CRUD. Se usan los valores del array.')
                            ->example('App\\Entity\\User::getRolesDetalle')
                            ->validate()
                                ->ifTrue(static fn ($value) => !str_contains((string) $value, '::'))
                                ->thenInvalid('gales_maker.crud.roles_source debe tener la forma "Clase::metodo" o "Clase::CONSTANTE"; se recibió %s.')
                            ->end()
                        ->end()
                        ->arrayNode('default_roles')
                            ->info('Roles que siempre tienen acceso a un CRUD generado; los que se elijan al generarlo se suman a estos.')
                            ->scalarPrototype()
                                ->validate()
                                    ->ifTrue(static fn ($role) => 1 !== preg_match(RoleNormalizer::PATTERN, (string) $role))
                                    ->thenInvalid('%s no es un rol válido: debe empezar con ROLE_ y usar sólo mayúsculas, números y guion bajo.')
                                ->end()
                            ->end()
                            ->requiresAtLeastOneElement()
                            ->defaultValue(['ROLE_ADMIN'])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}