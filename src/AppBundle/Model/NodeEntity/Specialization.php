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
class Specialization extends BaseModel
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

    /**
     * @OGM\Relationship(type="BELONGS_TO", direction="INCOMING", collection=true, mappedBy="specialization", targetEntity="Subject")
     *
     * @var Subject[] | Collection
     */
    protected $subjects;

    /**
     * Specialization constructor.
     */
    public function __construct()
    {
        $this->series =  new Collection();
        $this->subjects = new Collection();
    }


    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     * @return Specialization
     */
    public function setDepartment($department)
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
     * @return Subject[]|Collection
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * @param Subject[]|Collection $subjects
     * @return Specialization
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
        return $this;
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
}