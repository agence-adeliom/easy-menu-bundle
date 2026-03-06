<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\EventListener;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\EventListener\MenuCreationListener;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\EventListener\MenuCreationListener::class)]
final class MenuCreationListenerTest extends TestCase
{
    public function testPrePersistAddsPublishedRootItem(): void
    {
        $menu = new TestMenu();
        $listener = new MenuCreationListener(TestMenu::class, TestMenuItem::class);

        $listener->prePersist($menu);

        self::assertCount(1, $menu->getItems());
        $rootItem = $menu->getItems()->first();
        self::assertSame($menu, $rootItem->getMenu());
        self::assertSame('Root', $rootItem->getName());
        self::assertSame(ThreeStateStatusEnum::PUBLISHED, $rootItem->getState());
    }
}
