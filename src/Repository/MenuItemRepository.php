<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class MenuItemRepository extends NestedTreeRepository implements ServiceEntityRepositoryInterface
{
    protected bool $cacheEnabled = false;
    protected int $cacheTtl = 0;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new \LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                $entityClass
            ));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * @param array{enabled: bool, ttl: int} $cacheConfig
     */
    public function setConfig(array $cacheConfig): void
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('menuitem')
            ->where('menuitem.state = :state')
            ->andWhere('menuitem.publishDate < :publishDate');

        $orExpression = $queryBuilder->expr()->orx();
        $orExpression->add($queryBuilder->expr()->gt('menuitem.unpublishDate', ':unpublishDate'));
        $orExpression->add($queryBuilder->expr()->isNull('menuitem.unpublishDate'));

        $queryBuilder->andWhere($orExpression);
        $queryBuilder->setParameter('state', ThreeStateStatusEnum::PUBLISHED);
        $queryBuilder->setParameter('publishDate', new \DateTime());
        $queryBuilder->setParameter('unpublishDate', new \DateTime());

        return $queryBuilder;
    }

    /**
     * @return MenuItemEntity[]|QueryBuilder
     */
    public function getPublished(bool $returnQueryBuilder = false): array|QueryBuilder
    {
        $queryBuilder = $this->getPublishedQuery();
        if ($returnQueryBuilder) {
            return $queryBuilder;
        }

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @return MenuItemEntity[]|QueryBuilder
     */
    public function getByMenu(MenuEntity $menuEntity, bool $returnQueryBuilder = false): array|QueryBuilder
    {
        $queryBuilder = $this->getPublishedQuery();
        $queryBuilder->andWhere('menuitem.menu = :menu')
            ->setParameter('menu', $menuEntity);

        if ($returnQueryBuilder) {
            return $queryBuilder;
        }

        return $this->fetchResults($queryBuilder);
    }

    /**
     * @return MenuItemEntity[]
     */
    protected function fetchResults(QueryBuilder $queryBuilder): array
    {
        return $this->configureCache($queryBuilder->getQuery())->getResult();
    }

    protected function configureCache(AbstractQuery $query): AbstractQuery
    {
        if ($this->cacheEnabled) {
            return $query->enableResultCache($this->cacheTtl);
        }

        return $query->disableResultCache();
    }
}
