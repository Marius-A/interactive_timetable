<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class SubSeries
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="SubSeries")
 */
class SubSeries extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="subSeries")
     *
     * @var Series
     */
    protected $series;

    /**
     * @var Calendar
     *
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="subSeries")
     */
    protected $calendar;
}