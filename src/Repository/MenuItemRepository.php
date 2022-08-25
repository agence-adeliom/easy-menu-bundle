<?php

namespace Adeliom\EasyMenuBundle\Repository;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyMenuBundle\Entity\MenuEntity;
use Adeliom\EasyMenuBundle\Entity\MenuItemEntity;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class MenuItemRepository extends NestedTreeRepository
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
        $this->cacheTtl     = $cacheConfig['ttl'];
    }

    public function getPublishedQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('menuitem')
            ->where('menuitem.state = :state')
            ->andWhere('menuitem.publishDate < :publishDate')
        ;

        $orModule = $qb->expr()->orx();
        $orModule->add($qb->expr()->gt('menuitem.unpublishDate', ':unpublishDate'));
        $orModule->add($qb->expr()->isNull('menuitem.unpublishDate'));

        $qb->andWhere($orModule);

        $qb->setParameter('state', ThreeStateStatusEnum::PUBLISHED());
        $qb->setParameter('publishDate', new \DateTime());
        $qb->setParameter('unpublishDate', new \DateTime());

        return $qb;
    }

    /**
     * @return MenuItemEntity[]
     */
    public function getPublished(bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        if ($returnQueryBuilder) {
            return $qb;
        }

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }

    /**
     * @return MenuItemEntity[]
     */
    public function getByMenu(MenuEntity $menuEntity, bool $returnQueryBuilder = false)
    {
        $qb = $this->getPublishedQuery();
        $qb->andWhere('menuitem.menu = :menu')
            ->setParameter('menu', $menuEntity)
        ;
        if ($returnQueryBuilder) {
            return $qb;
        }

        return $qb->getQuery()
            ->useResultCache($this->cacheEnabled, $this->cacheTtl)
            ->getResult();
    }
}
