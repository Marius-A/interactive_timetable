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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Specialization
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
}