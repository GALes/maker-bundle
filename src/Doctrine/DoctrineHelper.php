<?php


namespace GALes\MakerBundle\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use GALes\MakerBundle\Annotations\GalesMaker;
use GALes\MakerBundle\Attribute\OrderBy;
use Symfony\Bundle\MakerBundle\Util\PhpCompatUtil;
use GALes\MakerBundle\Doctrine\DoctrineHelperInterface;

class DoctrineHelper extends DoctrineHelperDecorator
{
    public function getMetadata(string $classOrNamespace = null, bool $disconnected = false)
    {
        // Agrego la metadata de GalesMaker: campo orderBy de entidades relacionadas

        /** @var ClassMetadata $metadata */
        $metadata = parent::getMetadata($classOrNamespace, $disconnected);

        // Store custom orderBy information in a custom property
        if (!isset($metadata->customOrderBy)) {
            $metadata->customOrderBy = [];
        }

        foreach ($metadata->associationMappings as $fieldName => $relation) {
            if ($relation['type'] === 2) {
                $targetEntity = $relation['targetEntity'];
                $reflClass = new \ReflectionClass($targetEntity);
                $orderByField = null;

                // Try PHP 8 attributes first
                $attributes = $reflClass->getAttributes(OrderBy::class);
                if (!empty($attributes)) {
                    $orderByAttribute = $attributes[0]->newInstance();
                    if (property_exists($targetEntity, $orderByAttribute->field)) {
                        $orderByField = $orderByAttribute->field;
                    }
                }

                // Fall back to annotations for backward compatibility
                if (!$orderByField) {
                    $reader = new AnnotationReader;
                    $classAnnotations = $reader->getClassAnnotations($reflClass);
                    foreach ($classAnnotations as $annot) {
                        if ($annot instanceof GalesMaker && $annot->getOrderBy() &&
                            property_exists($targetEntity, $annot->getOrderBy())
                        ) {
                            $orderByField = $annot->getOrderBy();
                            break;
                        }
                    }
                }

                // Store the orderBy field information
                if ($orderByField) {
                    $metadata->customOrderBy[$fieldName] = $orderByField;
                } else {
                    $metadata->customOrderBy[$fieldName] = $relation['joinColumns'][0]['referencedColumnName'];
                }
            }
        }
        return $metadata;
    }
}



//  /**
//   *  Implementación sin decorardor usando metodo magico de PHP
//  **/
//
//    private $doctrineHelper;
//
//    public function __construct(SymfonyDoctrineHelper $doctrineHelper)
//    {
//        $this->doctrineHelper = $doctrineHelper;
//    }
//
//    /**
//     * Si no encuentra el metodo en el objeto, entonces busca si existe como metodo del objeto $doctrineHelper
//     * (forma poco elegante de extender una clase final)
//     *
//     * @param $name
//     * @param $argument
//     * @return false|mixed
//     */
//    public function __call($name, $arguments)
//    {
//        return call_user_func_array([$this->doctrineHelper, $name], $arguments);
//    }