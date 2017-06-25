<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Student
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Student")
 */
class Student extends Person
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $email;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=false, mappedBy="students", targetEntity="SubSeries")
     * @var SubSeries
     */
    protected $subSeries;

    /**
     * Student constructor.
     * @param string $name
     * @param string $surname
     * @param string $email
     * @param SubSeries $subSeries
     */
    public function __construct(string $name, string $surname, string $email, SubSeries $subSeries)
    {
        parent::__construct($name, $surname);
        $this->email = $email;
        $this->subSeries = $subSeries;
    }

    /**
     * @return SubSeries
     */
    public function getSubSeries()
    {
        return $this->subSeries;
    }

    /**
     * @param SubSeries $subSeries
     */
    public function setSubSeries(SubSeries $subSeries)
    {
        $this->subSeries = $subSeries;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Student
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
        return $this;
    }
}