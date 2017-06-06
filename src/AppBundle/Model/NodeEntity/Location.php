<?php

namespace AppBundle\Model\NodeEntity;

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

    /**
     * Location constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Location
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}