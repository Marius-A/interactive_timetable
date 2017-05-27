<?php

namespace AppBundle\Model\NodeEntity;

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
    private $name;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=true, mappedBy="series", targetEntity="SubSeries")
     * @var  SubSeries[] | Collection
     */
    private $subSeries;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="series", targetEntity="Specialization")
     * @var  Specialization
     */
    private $specialization;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Series
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return SubSeries[]|Collection
     */
    public function getSubSeries()
    {
        return $this->subSeries;
    }

    /**
     * @param SubSeries[]|Collection $subSeries
     * @return Series
     */
    public function setSubSeries($subSeries)
    {
        $this->subSeries = $subSeries;
        return $this;
    }

    /**
     * @return Specialization
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * @param Specialization $specialization
     * @return Series
     */
    public function setSpecialization($specialization)
    {
        $this->specialization = $specialization;
        return $this;
    }
}