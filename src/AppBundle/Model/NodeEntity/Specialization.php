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
     *
     * @var Department
     */
    protected $department;

    /**
     * @OGM\Relationship(type="HAVE_SERIES", direction="OUTGOING", collection=true, mappedBy="specialization", targetEntity="Series")
     *
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
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return Department
     */
    public function getDepartment(): Department
    {
        return $this->department;
    }

    /**
     * @return Series[]|Collection
     */
    public function getSeries()
    {
        return $this->series;
    }
}