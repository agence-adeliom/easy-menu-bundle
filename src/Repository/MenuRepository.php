<?php

declare(strict_types=1);

namespace Adeliom\EasyMenuBundle\Repository;

use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

class MenuRepository extends ServiceEntityRepository
{
    protected bool $cacheEnabled = false;
    protected int $cacheTtl = 0;

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
        $queryBuilder = $this->createQueryBuilder('menu')
            ->where('menu.status = :status');

        $queryBuilder->setParameter('status', true);

        return $queryBuilder;
    }

    /**
     * @return MenuEntity[]
     */
    public function getPublished(): array
    {
        return $this->fetchResults($this->getPublishedQuery());
    }

    /**
     * @return MenuEntity[]
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
