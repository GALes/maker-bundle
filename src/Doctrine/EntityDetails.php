<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as LegacyClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Bundle\MakerBundle\Str;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 *
 * @internal
 */
final class EntityDetails
{
    private $metadata;

    /**
     * @param ClassMetadata|LegacyClassMetadata $metadata
     */
    public function __construct($metadata)
    {
        $this->metadata = $metadata;
    }

    public function getRepositoryClass()
    {
        return $this->metadata->customRepositoryClassName;
    }

    public function getIdentifier()
    {
        return $this->metadata->identifier[0];
    }

    public function getDisplayFields()
    {
        return $this->metadata->fieldMappings;
    }

    public function getFullDisplayFields()
    {
        $retorno = $this->metadata->fieldMappings;
//        dd($this->metadata->associationMappings);
        foreach ($this->metadata->associationMappings as $fieldName => $relation) {
            // Solo se permite mostrar los campos si la relacion es del tipo 2: ManyToOne,
            // no se permiten 4: OneToMany ni 8: ManyToMany
            if ($relation['type'] === 2) {
                $retorno[$fieldName] = $relation;
            }
        }

        return $retorno;
    }

    public function getFormFields()
    {
        $fieldsWithTypes = [];
        
        foreach ($this->metadata->fieldMappings as $fieldName => $metadata) {
            // agrego la metadata de cada campo
            $fieldsWithTypes[$fieldName] = ['metadata' => $metadata];

            // Si es del tipo datetime o date se agrega el arreglo de opciones para el datepicker
            if ($metadata['type'] == 'datetime' || $metadata['type'] == 'date') {
                $fieldsWithTypes[$fieldName]['type'] = "Symfony\\Component\\Form\\Extension\\Core\\Type\\" . ($metadata['type'] == 'datetime' ? 'DateTimeType' : 'DateType');
                $fieldsWithTypes[$fieldName]['options_code'] =
        "                'widget'    => 'single_text',\n" .
        "                'format'    => " . ($metadata['type'] == 'datetime' ? "'YYYY-MM-dd HH:mm'" : "'YYYY-MM-dd'") . ",\n" .
        "                'attr'      => ['class' => '" . $metadata['type'] . "picker'],"
                ;
            }
        }
        
        // Remove the primary key field if it's not managed manually
        // MOD: Agrego el campo como campo del tipo identifier
        if (!$this->metadata->isIdentifierNatural()) {
            foreach ($this->metadata->identifier as $identifier) {
                $fieldsWithTypes[$identifier]['metadata']['identifier'] = true;
            }
        }

        // TODO: Por el momento no se soportan declaraciones Doctrine Embeddables dentro del objeto
        if (!empty($this->metadata->embeddedClasses)) {
            dump('No se soportan declaraciones Doctrine Embeddables dentro del objeto, serán omitidas');
//            foreach (array_keys($this->metadata->embeddedClasses) as $embeddedClassKey) {
//                $fields = array_filter($fields, function ($v) use ($embeddedClassKey) {
//                    return 0 !== strpos($v, $embeddedClassKey.'.');
//                });
//            }
        }

        foreach ($this->metadata->associationMappings as $fieldName => $relation) {
            $fieldsWithTypes[$fieldName] = ['metadata' => $relation];
            $fieldsWithTypes[$fieldName]['metadata']['identifier'] = 'false';
        }

        return $fieldsWithTypes;
    }

    public function getLexikFormFields()
    {
        $fieldsWithTypes = [];

        foreach ($this->metadata->fieldMappings as $fieldName => $metadata) {
            // agrego la metadata de cada campo
            $fieldsWithTypes[$fieldName] = ['metadata' => $metadata];

            switch ($metadata['type']) {
                case 'integer':
                case 'float':
                    $fieldsWithTypes[$fieldName]['type'] = "Lexik\\Bundle\\FormFilterBundle\\Filter\\Form\\Type\\NumberFilterType";
                  break;
                case 'boolean':
                    $fieldsWithTypes[$fieldName]['type'] = "Lexik\\Bundle\\FormFilterBundle\\Filter\\Form\\Type\\ChoiceFilterType";
                    $fieldsWithTypes[$fieldName]['options_code'] =
                        "                \n" .
                        "                'choices' => [\n" .
                        "                    '' => '',\n" .
                        "                    'Si' => 1,\n" .
                        "                    'No' => 0,\n" .
                        "                ],\n" .
                        "//                'label' => '(Nombre del campo)',\n" .
                        "                'placeholder' => '',\n" .
                        "                'empty_data' => '',\n" .
                        "                'required' => false"
                    ;
                  break;
                case 'datetime':
                case 'date':
                    $fieldsWithTypes[$fieldName]['type'] = "Lexik\\Bundle\\FormFilterBundle\\Filter\\Form\\Type\\DateTimeFilterType";
                    $fieldsWithTypes[$fieldName]['options_code'] =
                        "                'apply_filter' => function (QueryInterface \$filterQuery, \$field, \$values) {\n" .
                        "                    if (empty(\$values['value'])) {\n" .
                        "                        return null;\n" .
                        "                    }\n" .
                        "                    \n" .
                        "                    \$paramName = sprintf('p_%s', str_replace('.', '_', \$field));\n" .
                        "                    \$paramName2 = sprintf('p_%s_2', str_replace('.', '_', \$field));\n" .
                        "                    \n" .
                        "                    \$field = explode('.', \$field)[0] . '." . $fieldName . "';\n" .
                        "                    \$expression = \$filterQuery->getExpr()->andX(\n" .
                        "                        \$filterQuery->getExpr()->gte(\$field, ':'.\$paramName),\n" .
                        "                        \$filterQuery->getExpr()->lte(\$field, ':'.\$paramName2)\n" .
                        "                    );\n" .
                        "                    \$parameters = [\n" .
                        "                        \$paramName => \$values['value'],\n" .
                        "                        \$paramName2 => (clone \$values['value'])->setTime(23, 59, 59, 999999),\n" .
                        "                    ];\n" .
                        "                    \n" .
                        "                    return \$filterQuery->createCondition(\$expression, \$parameters);\n" .
                        "                },\n" .
                        "//                'label' => 'Fecha Desde',\n" .
                        "                'widget' => 'single_text',\n" .
                        "                'html5' => false,\n" .
                        "                'attr' => ['class' => 'datepicker'],\n" .
                        "                'format' => 'YYYY-MM-dd',\n" .
                        "//                'data' => new \DateTime('2020-03-18')\n" .
                        "                'required' => false\n"
                    ;
                  break;
                case 'string':
                case 'text':
                default:
                    $fieldsWithTypes[$fieldName]['type'] = "Lexik\\Bundle\\FormFilterBundle\\Filter\\Form\\Type\\TextFilterType";
                    $fieldsWithTypes[$fieldName]['options_code'] =
                        "                'condition_pattern'    => FilterOperands::STRING_CONTAINS,"
                    ;
                  break;
            }
        }

        // Remove the primary key field if it's not managed manually
        // MOD: Agrego el campo como campo del tipo identifier
        if (!$this->metadata->isIdentifierNatural()) {
            foreach ($this->metadata->identifier as $identifier) {
                $fieldsWithTypes[$identifier]['metadata']['identifier'] = true;
            }
        }

        // TODO: Por el momento no se soportan declaraciones Doctrine Embeddables dentro del objeto
        if (!empty($this->metadata->embeddedClasses)) {
            dump('No se soportan declaraciones Doctrine Embeddables dentro del objeto, serán omitidas');
//            foreach (array_keys($this->metadata->embeddedClasses) as $embeddedClassKey) {
//                $fields = array_filter($fields, function ($v) use ($embeddedClassKey) {
//                    return 0 !== strpos($v, $embeddedClassKey.'.');
//                });
//            }
        }

//        dump($fieldsWithTypes);
//        dd($this->metadata->associationMappings);
        foreach ($this->metadata->associationMappings as $fieldName => $relation) {
//                  TODO: Implementar tipo Class (Joins)
            switch ($relation['type']) {
                case 2: // ManyToOne Join
                    $fieldsWithTypes[$fieldName]['type'] = "Lexik\\Bundle\\FormFilterBundle\\Filter\\Form\\Type\\EntityFilterType";
                    $fieldsWithTypes[$fieldName]['options_code'] =
                        "                'class' => " . Str::getShortClassName($relation['targetEntity']) . "::class,\n" .
                        "                'placeholder'   => 'Seleccione',\n" .
                        "                'empty_data'    => null,\n" .
                        "//                'query_builder' => function (EntityRepository \$er) {\n" .
                        "//                    return \$er->createQueryBuilder('t')\n" .
                        "//                        ->orderBy('t.nombre', 'ASC');\n" .
                        "//                'choice_label' => 'nombre',\n"
                    ;
                    $fieldsWithTypes[$fieldName]['targetEntityJoin'] = $relation['targetEntity'];
                  break;
                default:
                    $fieldsWithTypes[$fieldName] = ['metadata' => $relation];
                    $fieldsWithTypes[$fieldName]['metadata']['identifier'] = 'false';
            }
        }

        return $fieldsWithTypes;
    }
}
