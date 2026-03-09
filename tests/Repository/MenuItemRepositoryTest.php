<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Tests\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Repository\MenuItemRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyMenuBundle\Repository\MenuItemRepository::class)]
final class MenuItemRepositoryTest extends TestCase
{
    public function testConstructorThrowsWhenNoEntityManagerSupportsClass(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(MenuEntity::class)
            ->willReturn(null);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Could not find the entity manager for class "Adeliom\\EasyMenuBundle\\Entity\\MenuEntity".');

        new MenuItemRepository($registry, MenuEntity::class);
    }

    public function testGetPublishedQueryFiltersStateAndPublicationWindow(): void
    {
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::once())->method('where')->with('menuitem.state = :state')->willReturnSelf();
        $builder->expects(self::exactly(2))->method('andWhere')->willReturnSelf();
        $parameters = [];
        $builder->expects(self::exactly(3))->method('setParameter')
            ->willReturnCallback(function (string $key, mixed $value) use (&$parameters, $builder): QueryBuilder {
                $parameters[] = [$key, $value];

                return $builder;
            });

        $repository = new MenuItemRepositoryHarness($builder);

        self::assertSame($builder, $repository->getPublishedQuery());
        self::assertSame('state', $parameters[0][0]);
        self::assertSame(ThreeStateStatusEnum::PUBLISHED, $parameters[0][1]);
        self::assertSame('publishDate', $parameters[1][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[1][1]);
        self::assertSame('unpublishDate', $parameters[2][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[2][1]);
    }

    public function testGetPublishedReturnsBuilderOrCachedResultsDependingOnFlag(): void
    {
        $repository = new MenuItemRepositoryHarness($this->createConfiguredBuilder());
        $repository->setConfig(['enabled' => true, 'ttl' => 120]);
        $repository->setResults(['item']);

        self::assertInstanceOf(QueryBuilder::class, $repository->getPublished(true));
        self::assertSame(['item'], $repository->getPublished());
        self::assertTrue($repository->wasCacheEnabled());
        self::assertSame(120, $repository->getObservedCacheTtl());
    }

    public function testGetByMenuReturnsBuilderOrResultsDependingOnFlagAndCache(): void
    {
        $menu = new MenuEntity();
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::exactly(3))->method('andWhere')->willReturnSelf();
        $parameters = [];
        $builder->expects(self::exactly(4))->method('setParameter')
            ->willReturnCallback(function (string $key, mixed $value) use (&$parameters, $builder): QueryBuilder {
                $parameters[] = [$key, $value];

                return $builder;
            });

        $repository = new MenuItemRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);

        self::assertSame($builder, $repository->getByMenu($menu, true));
        self::assertSame('state', $parameters[0][0]);
        self::assertSame(ThreeStateStatusEnum::PUBLISHED, $parameters[0][1]);
        self::assertSame('publishDate', $parameters[1][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[1][1]);
        self::assertSame('unpublishDate', $parameters[2][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[2][1]);
        self::assertSame(['menu', $menu], $parameters[3]);
    }

    public function testGetByMenuReturnsResultsWhenQueryBuilderIsNotRequested(): void
    {
        $menu = new MenuEntity();
        $builder = $this->createConfiguredBuilder();
        $builder->expects(self::exactly(3))->method('andWhere')->willReturnSelf();
        $parameters = [];
        $builder->expects(self::exactly(4))->method('setParameter')
            ->willReturnCallback(function (string $key, mixed $value) use (&$parameters, $builder): QueryBuilder {
                $parameters[] = [$key, $value];

                return $builder;
            });

        $repository = new MenuItemRepositoryHarness($builder);
        $repository->setConfig(['enabled' => false, 'ttl' => 300]);
        $repository->setResults(['item']);

        self::assertSame(['item'], $repository->getByMenu($menu));
        self::assertSame('state', $parameters[0][0]);
        self::assertSame(ThreeStateStatusEnum::PUBLISHED, $parameters[0][1]);
        self::assertSame('publishDate', $parameters[1][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[1][1]);
        self::assertSame('unpublishDate', $parameters[2][0]);
        self::assertInstanceOf(\DateTime::class, $parameters[2][1]);
        self::assertSame(['menu', $menu], $parameters[3]);
        self::assertFalse($repository->wasCacheEnabled());
        self::assertSame(300, $repository->getObservedCacheTtl());
    }

    private function createConfiguredBuilder(): QueryBuilder
    {
        $builder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where', 'andWhere', 'setParameter', 'expr'])
            ->getMock();

        $builder->method('where')->willReturnSelf();
        $builder->method('andWhere')->willReturnSelf();
        $builder->method('setParameter')->willReturnSelf();
        $builder->method('expr')->willReturn(new Expr());

        return $builder;
    }
}

final class MenuItemRepositoryHarness extends MenuItemRepository
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
