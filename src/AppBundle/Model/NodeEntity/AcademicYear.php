<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class AcademicYear
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="AcademicYear")
 */
class AcademicYear extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var  string
     */
    private $name;

    /**
     * @OGM\Relationship(type="HAVE", direction="INCOMING", collection=true, mappedBy="academicYear", targetEntity="Semester")
     * @var Semester[] | Collection
     */
    private $semesters;

    /**
     * AcademicYear constructor.
     * @example 2016-2017
     * @param  string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->semesters = new Collection();

        $this->semesters->add(new Semester(1, $this));
        $this->semesters->add(new Semester(2, $this));
    }

    /**
     * @param string $name
     * @return AcademicYear
     */
    public function setName(string $name): AcademicYear
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Semester[]|Collection
     */
    public function getSemesters()
    {
        return $this->semesters;
    }

    /**
     * @param Semester[]|Collection $semesters
     * @return AcademicYear
     */
    public function setSemesters($semesters)
    {
        $this->semesters = $semesters;
        return $this;
    }
}