<?php

namespace GALes\MakerBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
class GalesMaker implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    private $orderBy = null;

    public function __construct(string $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }
}