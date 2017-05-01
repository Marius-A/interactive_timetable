<?php

namespace AppBundle\Model\NodeEntity;


use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Specialization
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Specialization")
 */
class Specialization extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="PART_OF", direction="INCOMING", collection=false, mappedBy="specialization", targetEntity="Department")
     *
     * @var Department
     */
    protected $department;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=true, mappedBy="specialization", targetEntity="Series")
     *
     * @var Series[] | Collection
     */
    protected $series;

    /**
     * @OGM\Relationship(type="BELONGS", direction="OUTGOING", collection=true, mappedBy="specialization", targetEntity="Subject")
     *
     * @var Subject[] | Collection
     */
    protected $subjects;
}