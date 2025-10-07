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