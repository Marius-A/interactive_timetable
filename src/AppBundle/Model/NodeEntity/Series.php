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
class Series extends Participant
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

    /** @OGM\Label(name="Participant") */
    protected $canBeParticipant = true;

    /**
     * Series constructor.
     * @param string $name
     * @param int $yearOfStudy
     * @param Specialization $specialization
     */
    public function __construct($name, $yearOfStudy, Specialization $specialization)
    {
        $this->name = $name;
        $this->yearOfStudy = $yearOfStudy;
        $this->specialization = $specialization;
        $this->subSeries = new Collection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getYearOfStudy(): int
    {
        return $this->yearOfStudy;
    }

    /**
     * @return Specialization
     */
    public function getSpecialization(): Specialization
    {
        return $this->specialization;
    }

    /**
     * @return SubSeries[]|Collection
     */
    public function getSubSeries()
    {
        return $this->subSeries;
    }

    /**
     * @return mixed
     */
    public function getCanBeParticipant()
    {
        return $this->canBeParticipant;
    }
}