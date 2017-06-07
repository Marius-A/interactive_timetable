<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Specialization
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Specialization")
 */
class Specialization extends Participant
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $shortName;

    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $fullName;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=false, mappedBy="specialization", targetEntity="Department")
     * @var Department
     */
    protected $department;

    /**
     * @OGM\Relationship(type="HAVE_SERIES", direction="OUTGOING", collection=true, mappedBy="specialization", targetEntity="Series")
     * @var Series[] | Collection
     */
    protected $series;

    /** @OGM\Label(name="Participant") */
    protected $canBeParticipant = true;

    /**
     * Specialization constructor.
     * @param string $shortName
     * @param string $fullName
     * @param Department $department
     */
    public function __construct($shortName, $fullName, Department $department)
    {
        parent::__construct();
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->department = $department;
        $this->series =  new Collection();
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Specialization
     */
    public function setShortName(string $shortName): Specialization
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return Specialization
     */
    public function setFullName(string $fullName): Specialization
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment(): Department
    {
        return $this->department;
    }

    /**
     * @param Department $department
     * @return Specialization
     */
    public function setDepartment(Department $department): Specialization
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return Series[]|Collection
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param Series[]|Collection $series
     * @return Specialization
     */
    public function setSeries($series)
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanBeParticipant()
    {
        return $this->canBeParticipant;
    }

    /**
     * @param mixed $canBeParticipant
     * @return Specialization
     */
    public function setCanBeParticipant($canBeParticipant)
    {
        $this->canBeParticipant = $canBeParticipant;
        return $this;
    }
}