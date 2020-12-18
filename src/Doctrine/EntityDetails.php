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
}
