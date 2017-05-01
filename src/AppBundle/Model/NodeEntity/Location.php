<?php

namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Location
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Location")
 */
class Location extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

}