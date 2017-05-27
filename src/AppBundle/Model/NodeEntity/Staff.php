<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Staff
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Staff")
 */
abstract class Staff extends Person
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $email;
}