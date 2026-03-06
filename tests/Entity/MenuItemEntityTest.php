<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\Entity\MenuItemEntity::class)]
final class MenuItemEntityTest extends TestCase
{
    public function testMenuItemStoresHierarchyAndPublishedChildren(): void
    {
        $menu = new MenuEntity();
        $menu->setName('Header');

        $parent = new MenuItemEntity();
        $parent->setName('Parent');
        $parent->setMenu($menu);
        $parent->setUrl('/parent');
        $parent->setClassAttribute('nav-item');
        $parent->setPosition(1);
        $parent->setTarget(true);
        $parent->setLft(1);
        $parent->setLvl(0);
        $parent->setRgt(4);
        $parent->setRoot(1);

        $publishedChild = new MenuItemEntity();
        $publishedChild->setName('Published');
        $publishedChild->setState(ThreeStateStatusEnum::PUBLISHED);
        $publishedChild->setParent($parent);

        $pendingChild = new MenuItemEntity();
        $pendingChild->setName('Pending');
        $pendingChild->setState(ThreeStateStatusEnum::PENDING);
        $pendingChild->setParent($parent);

        self::assertSame($menu, $parent->getMenu());
        self::assertSame('/parent', $parent->getUrl());
        self::assertSame('nav-item', $parent->getClassAttribute());
        self::assertSame(1, $parent->getPosition());
        self::assertTrue($parent->isTarget());
        self::assertSame(1, $parent->getLft());
        self::assertSame(0, $parent->getLvl());
        self::assertSame(4, $parent->getRgt());
        self::assertSame(1, $parent->getRoot());
        self::assertTrue($parent->hasChild());
        self::assertFalse($parent->hasParent());
        self::assertTrue($publishedChild->hasParent());
        self::assertCount(2, $parent->getChildren());
        self::assertCount(1, $parent->getPublishedChildren());
        self::assertSame('Published', (string) $publishedChild);
        self::assertSame('Parent / Published', $publishedChild->getFlattenParents());
        self::assertSame('Pending', $pendingChild->getSortableData('name'));

        $parent->removeChild($pendingChild);
        self::assertCount(1, $parent->getChildren());

        $parent->setChildren(new ArrayCollection([$publishedChild, $pendingChild]));
        self::assertCount(2, $parent->getChildren());
    }

    public function testMenuItemPreRemoveUnpublishesState(): void
    {
        $item = new MenuItemEntity();
        $item->setState(ThreeStateStatusEnum::PUBLISHED);
        $item->onRemove();

        self::assertSame(ThreeStateStatusEnum::UNPUBLISHED, $item->getState());
    }
}
