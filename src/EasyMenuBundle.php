<?php

namespace Adeliom\EasyMenuBundle;

use Adeliom\EasyMenuBundle\DependencyInjection\EasyMenuExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyMenuBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EasyMenuExtension();
    }
}
