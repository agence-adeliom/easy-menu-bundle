<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Repository;

use Adeliom\EasyMenuBundle\Repository\MenuRepository;
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
        $repository = new MenuRepositoryHarness($this->createConfiguredBuilder());
        $repository->setConfig(['enabled' => true, 'ttl' => 600]);
        $repository->setResults(['menu']);

        self::assertSame(['menu'], $repository->getPublished());
        self::assertTrue($repository->wasCacheEnabled());
        self::assertSame(600, $repository->getObservedCacheTtl());
    }

    public function testGetPublishedUsesDisabledCachePolicy(): void
    {
        $repository = new MenuRepositoryHarness($this->createConfiguredBuilder());
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);
        $repository->setResults(['menu']);

        self::assertSame(['menu'], $repository->getPublished());
        self::assertFalse($repository->wasCacheEnabled());
        self::assertSame(300, $repository->getObservedCacheTtl());
    }

    private function createConfiguredBuilder(): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'setParameter'])
            ->getMock();

        $builder->method('where')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();

        return $builder;
    }
}

final class MenuRepositoryHarness extends MenuRepository
{
    /** @var list<mixed> */
    private array $results = [];
    private ?bool $observedCacheEnabled = null;
    private ?int $observedCacheTtl = null;

    public function __construct(private QueryBuilder $builder)
    {
    }

    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->builder;
    }

    /**
     * @param list<mixed> $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function wasCacheEnabled(): bool
    {
        return $this->observedCacheEnabled ?? false;
    }

    public function getObservedCacheTtl(): ?int
    {
        return $this->observedCacheTtl;
    }

    protected function fetchResults(QueryBuilder $queryBuilder): array
    {
        $this->observedCacheEnabled = $this->cacheEnabled;
        $this->observedCacheTtl = $this->cacheTtl;

        return $this->results;
    }
}
