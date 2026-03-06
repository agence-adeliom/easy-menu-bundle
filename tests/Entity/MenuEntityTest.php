<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Entity;

use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\Entity\MenuEntity::class)]
final class MenuEntityTest extends TestCase
{
    public function testMenuMaintainsItemsAndRootItem(): void
    {
        $menu = new MenuEntity();
        $menu->setName('Header');
        $menu->setCode('header');

        $item = new MenuItemEntity();
        $item->setName('Home');

        $menu->addItem($item);
        $menu->setRootItem($item);

        self::assertSame('Header', $menu->getName());
        self::assertSame('header', $menu->getCode());
        self::assertCount(1, $menu->getItems());
        self::assertSame($menu, $item->getMenu());
        self::assertSame($item, $menu->getRootItem());
        self::assertSame('Header', (string) $menu);

        $menu->removeItem($item);

        self::assertCount(0, $menu->getItems());
        self::assertNull($item->getMenu());
    }

    public function testMenuPreRemoveDisablesStatus(): void
    {
        $menu = new MenuEntity();
        $menu->setStatus(true);
        $menu->onRemove();

        self::assertFalse($menu->getStatus());
    }
}
