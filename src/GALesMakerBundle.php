<?php

namespace GALes\MakerBundle;

use GALes\MakerBundle\DependencyInjection\GALesMakerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GALesMakerBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new GALesMakerExtension();
        }

        return $this->extension;
    }

}