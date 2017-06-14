<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Department
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Department")
 */
class Department extends BaseModel implements \JsonSerializable
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
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=false, mappedBy="departments", targetEntity="Faculty")
     * @var Faculty
     */
    protected $faculty;

    /**
     * @OGM\Relationship(type="PART_OF", direction="INCOMING", collection=true, mappedBy="department", targetEntity="Specialization")
     *
     * @var Specialization[] | Collection
     */
    protected $specializations;

    /**
     * Department constructor.
     * @param string $shortName
     * @param string $fullName
     * @param Faculty $faculty
     */
    public function __construct($shortName, $fullName, Faculty $faculty)
    {
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->faculty = $faculty;
        $this->specializations = new Collection();
    }


    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Department
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return Department
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return Faculty
     */
    public function getFaculty()
    {
        return $this->faculty;
    }

    /**
     * @param Faculty $faculty
     * @return Department
     */
    public function setFaculty($faculty)
    {
        $this->faculty = $faculty;
        return $this;
    }

    /**
     * @return Specialization[]|Collection
     */
    public function getSpecializations()
    {
        return $this->specializations;
    }

    /**
     * @param Specialization[]|Collection $specializations
     * @return Department
     */
    public function setSpecializations($specializations)
    {
        $this->specializations = $specializations;
        return $this;
    }


    function __toString()
    {
        return '{"short_name":"'.$this->shortName.'", "full_name" :"'. $this->fullName.'", "id":"'.$this->id.'"}';
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
            'fullName'=> $this->fullName,
            'faculty'=> $this->faculty,
            'specializations' => $this->specializations->toArray()
        );
    }
}