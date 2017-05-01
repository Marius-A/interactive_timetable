<?php

namespace AppBundle\Model\NodeEntity;


use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Faculty
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Faculty")
 */
class Faculty extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $shortName;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $fullName;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=true, mappedBy="faculty", targetEntity="Department")
     * @var Department[] | Collection
     */
    protected $departments;
}