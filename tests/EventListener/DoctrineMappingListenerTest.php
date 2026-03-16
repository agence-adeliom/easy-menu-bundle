<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\EventListener;

use Adeliom\EasyMenuBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\EventListener\DoctrineMappingListener::class)]
final class DoctrineMappingListenerTest extends TestCase
{
    public function testListenerMapsMenuRelations(): void
    {
        $metadata = new ClassMetadata(TestMenu::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));

        (new DoctrineMappingListener(TestMenu::class, TestMenuItem::class))->loadClassMetadata($event);

        $mapping = $metadata->getAssociationMapping('items');

        self::assertTrue($metadata->hasAssociation('items'));
        self::assertSame(TestMenuItem::class, $mapping->targetEntity);
        self::assertSame('menu', $mapping->mappedBy);
    }

    public function testListenerMapsMenuItemRelations(): void
    {
        $metadata = new ClassMetadata(TestMenuItem::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));
        $listener = new DoctrineMappingListener(TestMenu::class, TestMenuItem::class);

        $listener->loadClassMetadata($event);
        $listener->loadClassMetadata($event);

        self::assertTrue($metadata->hasAssociation('menu'));
        self::assertTrue($metadata->hasAssociation('parent'));
        self::assertTrue($metadata->hasAssociation('children'));
        self::assertCount(3, $metadata->getAssociationMappings());
        self::assertSame(['position' => 'ASC'], $metadata->getAssociationMapping('children')->orderBy());
        self::assertSame('CASCADE', $metadata->getAssociationMapping('parent')->joinColumns[0]->onDelete);
    }
}
