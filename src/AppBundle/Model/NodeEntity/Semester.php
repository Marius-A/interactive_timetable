<?php


namespace AppBundle\Model\NodeEntity;

/**
 * Class Semester
 * @package AppBundle\Model\NodeEntity
 */
class Semester
{
    /** @var  integer */
    private $number;
    /** @var  AcademicYear */
    private $academicYear;
    /** @var string */
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