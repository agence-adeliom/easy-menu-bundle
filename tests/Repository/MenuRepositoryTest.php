<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Repository;

use Adeliom\EasyMenuBundle\Repository\MenuRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\Repository\MenuRepository::class)]
final class MenuRepositoryTest extends TestCase
{
    public function testGetPublishedQueryFiltersEnabledMenus(): void
    {
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::once())->method('where')->with('menu.status = :status')->willReturnSelf();
        $builder->expects(self::once())->method('setParameter')->with('status', true)->willReturnSelf();

        $repository = new MenuRepositoryHarness($builder);

        self::assertSame($builder, $repository->getPublishedQuery());
    }

    public function testGetPublishedUsesEnabledCachePolicy(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('enableResultCache')->with(600)->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['menu']);

        $repository = new MenuRepositoryHarness($this->createConfiguredBuilder($query));
        $repository->setConfig(['enabled' => true, 'ttl' => 600]);

        self::assertSame(['menu'], $repository->getPublished());
    }

    public function testGetPublishedUsesDisabledCachePolicy(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('disableResultCache')->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn(['menu']);

        $repository = new MenuRepositoryHarness($this->createConfiguredBuilder($query));
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame(['menu'], $repository->getPublished());
    }

    private function createConfiguredBuilder(?Query $query = null): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'setParameter', 'getQuery'])
            ->getMock();

        $builder->method('where')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('getQuery')->willReturn($query ?? $this->createMock(Query::class));

        return $builder;
    }
}

final class MenuRepositoryHarness extends MenuRepository
{
    public function __construct(private QueryBuilder $builder)
    {
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->builder;
    }
}
