<?php

namespace Adeliom\EasyMenuBundle\EventListener;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;

class MenuCreationListener
{
    public function __construct(protected string $menuClass, protected string $menuItemClass)
    {
    }

    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function prePersist(MenuEntity $menu): void
    {
        /**
         * @var MenuItemEntity $rootItem
         */
        $rootItem = new $this->menuItemClass();
        $rootItem->setMenu($menu);
        $rootItem->setName('Root');
        $rootItem->setState(ThreeStateStatusEnum::PUBLISHED());

        $menu->addItem($rootItem);
    }
}
