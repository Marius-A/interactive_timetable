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
     * @OGM\Property(type="int")
     * @var int
     */
    private $yearOfStudy;

    /**
     * @OGM\Relationship(type="HAVE_SERIES", direction="INCOMING", collection=false, mappedBy="series", targetEntity="Specialization")
     * @var  Specialization
     */
    private $specialization;


    /**
     * @OGM\Relationship(type="HAVE_SUB_SERIES", direction="OUTGOING", collection=true, mappedBy="series", targetEntity="SubSeries")
     * @var  SubSeries[] | Collection
     */
    private $subSeries;

    /**
     * Series constructor.
     */
    public function __construct()
    {
        $this->subSeries = new Collection();
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

    /**
     * @return int
     */
    public function getYearOfStudy(): int
    {
        return $this->yearOfStudy;
    }

    /**
     * @param int $yearOfStudy
     * @return Series
     */
    public function setYearOfStudy(int $yearOfStudy): Series
    {
        $this->yearOfStudy = $yearOfStudy;
        return $this;
    }
}