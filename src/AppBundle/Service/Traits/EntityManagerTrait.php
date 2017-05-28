<?php

namespace AppBundle\Service\Traits;

use GraphAware\Neo4j\OGM\EntityManager;

/**
 * trait EntityManagerTrait
 * @package AppBundle\Service\Traits
 */
trait EntityManagerTrait
{
    /** @var  EntityManager */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return EntityManagerTrait
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }
}