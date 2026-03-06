<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\EventListener;

use Adeliom\EasyMenuBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenu;
use Adeliom\EasyMenuBundle\Tests\Fixtures\Entity\TestMenuItem;
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
        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->method('getClassMetadata')->willReturn($metadata);

        (new DoctrineMappingListener(TestMenu::class, TestMenuItem::class))->loadClassMetadata($event);

        self::assertTrue($metadata->hasAssociation('items'));
    }

    public function testListenerMapsMenuItemRelations(): void
    {
        $metadata = new ClassMetadata(TestMenuItem::class);
        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->method('getClassMetadata')->willReturn($metadata);

        (new DoctrineMappingListener(TestMenu::class, TestMenuItem::class))->loadClassMetadata($event);

        self::assertTrue($metadata->hasAssociation('menu'));
        self::assertTrue($metadata->hasAssociation('parent'));
        self::assertTrue($metadata->hasAssociation('children'));
    }
}
