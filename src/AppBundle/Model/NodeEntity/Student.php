<?php

namespace AppBundle\Model\NodeEntity;


/**
 * Class Student
 * @package AppBundle\Model\NodeEntity
 */
class Student extends Person
{
    /**
     * OGM\Property(type="int")
     * @var integer
     */
    private $yearOfStudy;

    /**
     * @OGM\Relationship(type="PART_OF", collection=false, mappedBy="students", targetEntity="SubSeries")
     * @var SubSeries
     */
    protected $subSeries;

    /**
     * Student constructor.
     * @param string $name
     * @param string $surname
     * @param int $yearOfStudy
     * @param SubSeries $subSeries
     */
    public function __construct(string $name, string $surname, int $yearOfStudy, SubSeries $subSeries)
    {
        parent::__construct($name, $surname);
        $this->yearOfStudy = $yearOfStudy;
        $this->subSeries = $subSeries;
    }


    /**
     * @return int
     */
    public function getYearOfStudy(): int
    {
        return $this->yearOfStudy;
    }

    /**
     * @return SubSeries
     */
    public function getSubSeries(): SubSeries
    {
        return $this->subSeries;
    }
}