<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GALes\MakerBundle\Renderer;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @internal
 */
final class FormTypeRenderer
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function render(ClassNameDetails $formClassDetails, array $formFields, ClassNameDetails $boundClassDetails = null, array $constraintClasses = [], array $extraUseClasses = [], $templateName = 'form/Type.tpl.php')
    {
        $fieldTypeUseStatements = [];
        $fields = [];
        foreach ($formFields as $name => $fieldTypeOptions) {
            if ( !isset($fieldTypeOptions['type']) ) {
                $fieldTypeOptions['type'] = null;
            }
            if ( !isset($fieldTypeOptions['options_code']) ) {
                $fieldTypeOptions['options_code'] = null;
            }

            if (isset($fieldTypeOptions['type'])) {
                $fieldTypeUseStatements[] = $fieldTypeOptions['type'];
                $fieldTypeOptions['type'] = Str::getShortClassName($fieldTypeOptions['type']);
            }

            $fields[$name] = $fieldTypeOptions;
        }

        $mergedTypeUseStatements = array_unique(array_merge($fieldTypeUseStatements, $extraUseClasses));
        sort($mergedTypeUseStatements);
        
        $this->generator->generateClass(
            $formClassDetails->getFullName(),
            $templateName,
            [
                'bounded_full_class_name' => $boundClassDetails ? $boundClassDetails->getFullName() : null,
                'bounded_class_name' => $boundClassDetails ? $boundClassDetails->getShortName() : null,
                'form_fields' => $fields,
                'field_type_use_statements' => $mergedTypeUseStatements,
                'constraint_use_statements' => $constraintClasses,
            ]
        );
    }
}
