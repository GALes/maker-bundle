<?php

namespace GALes\MakerBundle;

use GALes\MakerBundle\DependencyInjection\GALesMakerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GALesMakerBundle extends Bundle
{
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new GALesMakerExtension();
        }

        return $this->extension;
    }

}