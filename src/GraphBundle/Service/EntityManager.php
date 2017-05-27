<?php


namespace GraphBundle\Service;

use GraphAware\Neo4j\Client\ClientBuilder;
use \GraphAware\Neo4j\OGM\EntityManager as GraphEntityManager;

/**
 * Class EntityManager
 * @package GraphBundle\Service
 */
class EntityManager extends GraphEntityManager
{
    /** @const */
    const SERVICE_NAME = 'graph.entity_manager.service';

    /**
     * EntityManager constructor.
     * @param string $host
     */
    public function __construct($host, $cacheDirectory)
    {
        $client = ClientBuilder::create()
            ->addConnection('default', $host)
            ->build();

        parent::__construct($client, $cacheDirectory, null);
    }
}