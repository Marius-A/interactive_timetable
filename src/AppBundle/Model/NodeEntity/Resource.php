<?php

namespace AppBundle\Model;

use AppBundle\Model\NodeEntity\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Resource
 * @package AppBundle\Model
 *
 * @OGM\Node(label="Resource")
 */
class Resource extends BaseModel
{

    //todo check if still needed
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;
}