<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Teacher
 * @package AppBundle\Model\NodeEntity
 * @OGM\Node(label="Teacher")
 */
class Teacher extends Staff
{
    /**
     * @OGM\Relationship(relationshipEntity="Teaching", direction="OUTGOING", collection=true, mappedBy="teacher")
     * @var  Course[] | Collection
     */
    protected $classes;


}