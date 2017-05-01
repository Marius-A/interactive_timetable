<?php

namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Series
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Series")
 */
class Series extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=true, mappedBy="series", targetEntity="SubSeries")
     * @var  SubSeries[] | Collection
     */
    protected $subSeries;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="series", targetEntity="Specialization")
     * @var  Specialization
     */
    protected $specialization;
}