<?php

use GALes\MakerBundle\Doctrine\DoctrineHelper;
use GALes\MakerBundle\GALesMaker;
use GALes\MakerBundle\Helper\GeneratorTwigHelper;
use GALes\MakerBundle\Maker\MakeCrud;
use GALes\MakerBundle\Renderer\CrudServiceRenderer;
use GALes\MakerBundle\Renderer\FormTypeRenderer;
use GALes\MakerBundle\Spreadsheet\Factory;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('maker.file_manager', FileManager::class)
        ->args([
            service('filesystem'),
            service('maker.autoloader_util'),
            service('maker.file_link_formatter'),
            '%kernel.project_dir%',
            '%twig.default_path%',
        ]);

    $services->set('gales.maker.gales_maker', GALesMaker::class)
        ->public();
    $services->alias(GALesMaker::class, 'gales.maker.gales_maker')
        ->private();

    $services->set('gales.maker.make_crud', MakeCrud::class)
        ->args([
            service('gales.maker.doctrine_helper'),
            service('gales.maker.renderer.form_type_renderer'),
            service('gales.maker.renderer.crud_service_renderer'),
            service('kernel'),
            service('gales.maker.generator_twig_helper'),
            '%security.role_hierarchy.roles%',
        ])
        ->tag('maker.command');

    $services->set('gales.maker.renderer.form_type_renderer', FormTypeRenderer::class)
        ->args([service('maker.generator')]);

    $services->set('gales.maker.renderer.crud_service_renderer', CrudServiceRenderer::class)
        ->args([
            service('maker.generator'),
            service('maker.file_manager'),
        ]);

    $services->set('gales.maker.generator_twig_helper', GeneratorTwigHelper::class)
        ->args([service('maker.file_manager')]);

    $services->set('gales.maker.doctrine_helper', DoctrineHelper::class)
        ->args([service('maker.doctrine_helper')]);

    $services->set('gales.maker.spreadsheet_factory', Factory::class);
    $services->alias(Factory::class, 'gales.maker.spreadsheet_factory')
        ->private();
};
