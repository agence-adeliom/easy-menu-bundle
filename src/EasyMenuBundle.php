<?php

namespace Adeliom\EasyMenuBundle;

use Adeliom\EasyMenuBundle\DependencyInjection\EasyMenuExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyMenuBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new EasyMenuExtension();
    }
}
