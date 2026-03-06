<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\DependencyInjection;

use Adeliom\EasyMenuBundle\DependencyInjection\Configuration;
use Adeliom\EasyMenuBundle\Repository\MenuItemRepository;
use Adeliom\EasyMenuBundle\Repository\MenuRepository;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestMenuItemRepository;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Repository\TestMenuRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

#[CoversClass(\Adeliom\EasyMenuBundle\DependencyInjection\Configuration::class)]
final class ConfigurationTest extends TestCase
{
    public function testConfigurationAcceptsCustomEntitiesAndAppliesDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
            'menu' => ['class' => TestMenu::class],
            'menu_item' => ['class' => TestMenuItem::class],
        ]]);

        self::assertSame(TestMenu::class, $config['menu']['class']);
        self::assertSame(MenuRepository::class, $config['menu']['repository']);
        self::assertSame(TestMenuItem::class, $config['menu_item']['class']);
        self::assertSame(MenuItemRepository::class, $config['menu_item']['repository']);
        self::assertFalse($config['cache']['enabled']);
        self::assertSame(300, $config['cache']['ttl']);
    }

    public function testConfigurationAcceptsCustomRepositories(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [[
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
        ]]);

        self::assertSame(TestMenuRepository::class, $config['menu']['repository']);
        self::assertSame(TestMenuItemRepository::class, $config['menu_item']['repository']);
        self::assertTrue($config['cache']['enabled']);
        self::assertSame(600, $config['cache']['ttl']);
    }

    public function testConfigurationRejectsInvalidMenuClass(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        (new Processor())->processConfiguration(new Configuration(), [[
            'menu' => ['class' => \stdClass::class],
            'menu_item' => ['class' => TestMenuItem::class],
        ]]);
    }
}
