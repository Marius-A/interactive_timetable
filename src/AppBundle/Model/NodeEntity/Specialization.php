<?php

namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\SpecializationCategory;
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
     * @OGM\Property(type="string")
     *
     * @var string
     * @see SpecializationCategory
     */
    protected $specializationCategory;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=false, mappedBy="specialization", targetEntity="Department")
     * @var Department
     */
    protected $department;

    /**
     * @OGM\Relationship(type="PART_OF", direction="INCOMING", collection=true, mappedBy="specialization", targetEntity="Series")
     * @var Series[] | Collection
     */
    protected $series;

    /** @OGM\Label(name="Participant") */
    protected $canBeParticipant = true;

    /**
     * Specialization constructor.
     * @param string $shortName
     * @param string $fullName
     * @param string $specializationCategory
     * @param Department $department
     */
    public function __construct(string $shortName,string $fullName, string $specializationCategory, Department $department)
    {
        parent::__construct();
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->specializationCategory = $specializationCategory;
        $this->department = $department;
        $this->series =  new Collection();
        $this->identifier = $this->shortName;
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
     * @return string
     */
    public function getSpecializationCategory(): string
    {
        return $this->specializationCategory;
    }

    /**
     * @param string $specializationCategory
     * @return Specialization
     */
    public function setSpecializationCategory(string $specializationCategory): Specialization
    {
        $this->specializationCategory = $specializationCategory;
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

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return array(
            'shortName' => $this->shortName,
            'fullName'=>$this->fullName,
            'specializationCategory' => $this->specializationCategory,
            'department'=>$this->department,
            'identifier'=>$this->identifier,
            'series' => $this->series->toArray()
        );
    }
}