<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Inflector\Inflector as LegacyInflector;
use Doctrine\Inflector\InflectorFactory;
use GALes\MakerBundle\Doctrine\EntityDetails;
use GALes\MakerBundle\Helper\GeneratorTwigHelper;
use GALes\MakerBundle\Renderer\ExportServiceRenderer;
use GALes\MakerBundle\Renderer\FormFilterTypeRenderer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use GALes\MakerBundle\Renderer\FormTypeRenderer;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;
use App\Kernel;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 */
final class MakeCrud extends AbstractMaker
{
    private $doctrineHelper;

    private $formTypeRenderer;

    private $exportServiceRenderer;

    private $inflector;

    private $appKernel;

    private $generatorTwigHelper;
    
    protected $availableRoles = array();

    public function __construct(
        DoctrineHelper          $doctrineHelper, 
        FormTypeRenderer        $formTypeRenderer, 
        ExportServiceRenderer   $exportServiceRenderer,
        Kernel                  $appKernel, 
        GeneratorTwigHelper     $generatorTwigHelper, 
        $roleHierarchy)
    {
        $this->doctrineHelper           = $doctrineHelper;
        $this->formTypeRenderer         = $formTypeRenderer;
        $this->exportServiceRenderer    = $exportServiceRenderer;
        $this->appKernel                = $appKernel;
        $this->generatorTwigHelper      = $generatorTwigHelper;
        
        $this->availableRoles           = $this->extractAvailableRoles($roleHierarchy);

        if (class_exists(InflectorFactory::class)) {
            $this->inflector = InflectorFactory::create()->build();
        }
    }

    public static function getCommandName(): string
    {
        return 'gales:make:crud';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates CRUD for Doctrine entity class')
            ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCrud.txt'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');

            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
            
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );

        $entityDoctrineDetails = new EntityDetails($this->doctrineHelper->getMetadata($entityClassDetails->getFullName()));
//        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
//        dump($entityDoctrineDetails);
        
//        dd($entityDoctrineDetails->getFormFields());

        $repositoryVars = [];

        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\'.$entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );

            $repositoryVars = [
                'repository_full_class_name'    => $repositoryClassDetails->getFullName(),
                'repository_class_name'         => $repositoryClassDetails->getShortName(),
                'repository_var'                => lcfirst($this->singularize($repositoryClassDetails->getShortName())),
            ];
        }

        $controllerClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix().'Controller',
            'Controller\\',
            'Controller'
        );

        $exportServiceClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix().'ExportService',
            'Service\\',
            'Service'
        );

        $iter = 0;
        do {
            $formClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix().($iter ?: '').'Type',
                'Form\\',
                'Type'
            );
            $formFilterClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix().($iter ?: '').'FilterType',
                'Form\\',
                'FilterType'
            );
            ++$iter;
        } while (class_exists($formClassDetails->getFullName()));

        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));

        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);

        $routeName = Str::asRouteName($controllerClassDetails->getRelativeNameWithoutSuffix());
        $templatesPath = Str::asFilePath($controllerClassDetails->getRelativeNameWithoutSuffix());

        $templatesBasePath = $this->appKernel->getProjectDir() . '/vendor/gales/maker-bundle/src/Resources/skeleton/';

        $this->exportServiceRenderer->generateClass(
            $exportServiceClassDetails->getFullName(),
            $templatesBasePath . 'service/ExportService.tpl.php',
            [
                'entity_class_name'             => $entityClassDetails->getShortName(),
                'entity_full_class_name'        => $entityClassDetails->getFullName(),
                'entity_var_plural'             => $entityVarPlural,
                'entity_var_singular'           => $entityVarSingular,
                'entity_fields'                 => $entityDoctrineDetails->getFormFields(),
                'custom_helper'                 => $this->generatorTwigHelper,
            ]
        );

        $generator->generateController(
            $controllerClassDetails->getFullName(),
            $templatesBasePath . 'crud/controller/Controller.tpl.php',
            array_merge([
                    'entity_full_class_name'            => $entityClassDetails->getFullName(),
                    'entity_class_name'                 => $entityClassDetails->getShortName(),
                    'form_full_class_name'              => $formClassDetails->getFullName(),
                    'form_class_name'                   => $formClassDetails->getShortName(),
                    'form_filter_full_class_name'       => $formFilterClassDetails->getFullName(),
                    'form_filter_class_name'            => $formFilterClassDetails->getShortName(),
                    'route_path'                        => Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()),
                    'route_name'                        => $routeName,
                    'templates_path'                    => $templatesPath,
                    'entity_var_plural'                 => $entityVarPlural,
                    'entity_twig_var_plural'            => $entityTwigVarPlural,
                    'entity_var_singular'               => $entityVarSingular,
                    'entity_twig_var_singular'          => $entityTwigVarSingular,
                    'entity_identifier'                 => $entityDoctrineDetails->getIdentifier(),
                    'export_service_full_class_name'    => $exportServiceClassDetails->getFullName(),
                ],
                $repositoryVars
            )
        );
        
//        dump($entityDoctrineDetails->getFormFields());
        $this->formTypeRenderer->render(
            $formClassDetails,
            $entityDoctrineDetails->getFormFields(),
            $entityClassDetails,
            [],
            [],
            $templatesBasePath . 'form/Type.tpl.php'
        );
        $this->formTypeRenderer->render(
            $formFilterClassDetails,
            $entityDoctrineDetails->getFormFields(),
            $entityClassDetails,
            [],
            [],
            $templatesBasePath . 'form/FilterType.tpl.php'
        );

        $templates = [
            '_delete_form' => [
                'route_name'                => $routeName,
                'entity_twig_var_singular'  => $entityTwigVarSingular,
                'entity_identifier'         => $entityDoctrineDetails->getIdentifier(),
                'custom_helper'             => $this->generatorTwigHelper,
            ],
            '_form' => [],
            'edit' => [
                'entity_class_name'         => $entityClassDetails->getShortName(),
                'entity_twig_var_singular'  => $entityTwigVarSingular,
                'entity_identifier'         => $entityDoctrineDetails->getIdentifier(),
                'route_name'                => $routeName,
                'custom_helper'             => $this->generatorTwigHelper
            ],
            'index' => [
                'entity_class_name'         => $entityClassDetails->getShortName(),
                'entity_twig_var_plural'    => $entityTwigVarPlural,
                'entity_twig_var_singular'  => $entityTwigVarSingular,
                'entity_identifier'         => $entityDoctrineDetails->getIdentifier(),
                'entity_fields'             => $entityDoctrineDetails->getDisplayFields(),
                'route_name'                => $routeName,
                'custom_helper'             => $this->generatorTwigHelper
            ],
            'new' => [
                'entity_class_name'         => $entityClassDetails->getShortName(),
                'entity_twig_var_singular'  => $entityTwigVarSingular,
                'entity_identifier'         => $entityDoctrineDetails->getIdentifier(),
                'route_name'                => $routeName,
                'custom_helper'             => $this->generatorTwigHelper
            ],
            'show' => [
                'entity_class_name'         => $entityClassDetails->getShortName(),
                'entity_twig_var_singular'  => $entityTwigVarSingular,
                'entity_identifier'         => $entityDoctrineDetails->getIdentifier(),
                'entity_fields'             => $entityDoctrineDetails->getDisplayFields(),
                'route_name'                => $routeName,
                'custom_helper'             => $this->generatorTwigHelper
            ],
        ];
        foreach ($templates as $template => $variables) {
            $generator->generateTemplate(
                $templatesPath.'/'.$template.'.html.twig',
                $templatesBasePath . 'crud/templates/'.$template.'.tpl.php',
                $variables
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(sprintf('Next: Check your new CRUD by going to <fg=yellow>%s/</>', Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix())));
    }

    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }

    private function pluralize(string $word): string
    {
        if (null !== $this->inflector) {
            return $this->inflector->pluralize($word);
        }

        return LegacyInflector::pluralize($word);
    }

    private function singularize(string $word): string
    {
        if (null !== $this->inflector) {
            return $this->inflector->singularize($word);
        }

        return LegacyInflector::singularize($word);
    }

    /**
     * Extract unique roles from role hierarchy
     *
     * @param type $roleHierarchy
     * @return array
     */
    protected function extractAvailableRoles($roleHierarchy)
    {
        // always add this roles first
        $availableRoles = array(
            'ROLE_USER',
            'ROLE_ADMIN'
        );

        array_walk_recursive($roleHierarchy, function($role) use (&$availableRoles) {
            if (array_search($role, $availableRoles) === FALSE) {
                $availableRoles[] = $role;
            }
        });

        return $availableRoles;
    }
}
