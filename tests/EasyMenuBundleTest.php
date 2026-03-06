<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests;

use Adeliom\EasyMenuBundle\DependencyInjection\EasyMenuExtension;
use Adeliom\EasyMenuBundle\EasyMenuBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\EasyMenuBundle::class)]
final class EasyMenuBundleTest extends TestCase
{
    public function testBundleReturnsExpectedContainerExtension(): void
    {
        $bundle = new EasyMenuBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(EasyMenuExtension::class, $extension);
        self::assertSame('easy_menu', $extension?->getAlias());
    }
}
