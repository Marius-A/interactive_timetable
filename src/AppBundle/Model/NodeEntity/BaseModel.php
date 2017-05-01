<?php

namespace AppBundle\Model;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class BaseModel
 * @package AppBundle\Model
 */
abstract class BaseModel
{
    /**
     * @OGM\GraphId()
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}