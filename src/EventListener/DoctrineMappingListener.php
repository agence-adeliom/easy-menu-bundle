<?php

namespace Adeliom\EasyMenuBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations in Page and Category entities,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    public function __construct(private string $menuClass, private string $menuItemClass)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();


        $isMenuItem  = is_a($classMetadata->getName(), $this->menuItemClass, true);
        $isMenu = is_a($classMetadata->getName(), $this->menuClass, true);

        if ($isMenuItem) {
            $this->processMenuItemMetadata($classMetadata);
        }

        if ($isMenu) {
            $this->processMenuMetadata($classMetadata);
        }
    }

    private function processMenuItemMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('menu')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'menu',
                'targetEntity' => $this->menuClass,
                'inversedBy' => 'items',
                'orderBy' => [
                    "position" => "ASC"
                ]
            ]);
        }

        if (!$classMetadata->hasAssociation('parent')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'parent',
                'targetEntity' => $this->menuItemClass,
                'inversedBy' => 'children',
                'cascade' => ['persist'],
                'isOnDeleteCascade' => false,
                'nullable' => true,
                'orderBy' => [
                    "position" => "ASC"
                ]
            ]);
        }

        if (!$classMetadata->hasAssociation('children')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'children',
                'targetEntity' => $this->menuItemClass,
                'mappedBy' => 'parent',
                'cascade' => ['all'],
                'orderBy' => [
                    "position" => "ASC"
                ]
            ]);
        }
    }

    private function processMenuMetadata(ClassMetadata $classMetadata): void
    {
        if (!$classMetadata->hasAssociation('items')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'items',
                'targetEntity' => $this->menuItemClass,
                'mappedBy' => 'menu',
                'cascade' => ['all'],
            ]);
        }
    }
}
