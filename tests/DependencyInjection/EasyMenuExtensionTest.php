<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\DependencyInjection;

use Adeliom\EasyMenuBundle\DependencyInjection\EasyMenuExtension;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestMenuItemRepository;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestMenuRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(\Adeliom\EasyMenuBundle\DependencyInjection\EasyMenuExtension::class)]
final class EasyMenuExtensionTest extends TestCase
{
    public function testExtensionLoadsParametersAndServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new EasyMenuExtension();

        $extension->load([[
            'menu' => [
                'class' => TestMenu::class,
                'repository' => TestMenuRepository::class,
            ],
            'menu_item' => [
                'class' => TestMenuItem::class,
                'repository' => TestMenuItemRepository::class,
            ],
            'cache' => [
                'enabled' => true,
                'ttl' => 600,
            ],
        ]], $container);

        self::assertSame('easy_menu', $extension->getAlias());
        self::assertSame(TestMenu::class, $container->getParameter('easy_menu.menu.class'));
        self::assertSame(TestMenuRepository::class, $container->getParameter('easy_menu.menu.repository'));
        self::assertSame(TestMenuItem::class, $container->getParameter('easy_menu.menu_item.class'));
        self::assertSame(['enabled' => true, 'ttl' => 600], $container->getParameter('easy_menu.cache'));
        self::assertTrue($container->hasDefinition('easy_menu.menu.repository'));
        self::assertTrue($container->hasDefinition('easy_menu.menu_item.repository'));
        self::assertTrue($container->hasDefinition('Adeliom\\EasyMenuBundle\\Twig\\EasyMenuExtension'));
    }
}
