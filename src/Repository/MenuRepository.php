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
