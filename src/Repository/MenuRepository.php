<?php

namespace Adeliom\EasyMenuBundle\Repository;

use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var int
     */
    protected $cacheTtl;

    public function setConfig(array $cacheConfig)
    {
        $this->cacheEnabled = $cacheConfig['enabled'];
        $this->cacheTtl = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('menu')
            ->where('menu.status = :status')
        ;

        $qb->setParameter('status', true);

        return $qb;
    }

    /**
     * @return MenuEntity[]
     */
    public function getPublished()
    {
        $qb = $this->getPublishedQuery();

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }
}
