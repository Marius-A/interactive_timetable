<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Semester
 * @package AppBundle\Model\NodeEntity
 * @OGM\Node(label="Semester")
 */
class Semester extends BaseModel
{
    /**
     * @OGM\Property(type="int")
     * @var  integer
     */
    private $number;
    /**
     * @OGM\Relationship(type="HAVE", direction="INCOMING", collection=false, mappedBy="semesters", targetEntity="AcademicYear")
     * @var  AcademicYear
     */
    private $academicYear;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    private $key;

    /**
     * Semester constructor.
     * @param int $number
     * @param AcademicYear $academicYear
     */
    public function __construct($number, AcademicYear $academicYear)
    {
        $this->number = $number;
        $this->academicYear = $academicYear;
        $this->key = 'Sem-'.$number;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return AcademicYear
     */
    public function getAcademicYear(): AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}