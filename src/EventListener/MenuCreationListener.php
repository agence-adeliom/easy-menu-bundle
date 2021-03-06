<?php

namespace Adeliom\EasyMenuBundle\EventListener;


use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class MenuCreationListener
{
    protected $menuClass;
    protected $menuItemClass;

    public function __construct(string $menuClass, string $menuItemClass)
    {
        $this->menuClass = $menuClass;
        $this->menuItemClass = $menuItemClass;
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
