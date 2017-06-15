<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Faculty
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Faculty")
 */
class Faculty extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $shortName;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $fullName;

    /**
     * @OGM\Relationship(type="PART_OF", direction="INCOMING", collection=true, mappedBy="faculty", targetEntity="Department")
     * @var Department[] | Collection
     */
    protected $departments;

    /**
     * Faculty constructor.
     */
    public function __construct()
    {
        $this->departments = new Collection();
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
     * @return Faculty
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
     * @return Faculty
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return Department[]|Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param Department[]|Collection $departments
     * @return Faculty
     */
    public function setDepartments($departments)
    {
        $this->departments = $departments;
        return $this;
    }

    function __toString()
    {
        return '{"short_name":"' . $this->shortName . '", "full_name" :"' . $this->fullName . '", "id":"' . $this->id . '"}';
    }

}