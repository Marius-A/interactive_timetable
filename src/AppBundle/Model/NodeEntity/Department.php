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
class Department extends BaseModel
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
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="faculty", targetEntity="Faculty")
     * @var Faculty
     */
    protected $faculty;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=true, mappedBy="department", targetEntity="Specialization")
     *
     * @var Specialization[] | Collection
     */
    protected $specializations;

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
}