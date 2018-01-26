<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\ParticipantType;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class SubSeries
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="SubSeries")
 */
class SubSeries extends Participant
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=false, mappedBy="subSeries")
     * @var Series
     */
    protected $series;

    /**
     * @OGM\Relationship(type="PART_OF", direction="INCOMING", collection=true, mappedBy="subSeries")
     * @var Student[] | Collection
     */
    protected $students;

    /** @OGM\Label(name="Participant") */
    protected $canBeParticipant = true;

    /**
     * SubSeries constructor.
     * @param string $name
     * @param Series $series
     */
    public function __construct($name, Series $series)
    {
        parent::__construct();
        $this->name = $name;
        $this->series = $series;
        $this->students = new Collection();
        $this->identifier = $this->series->identifier.'-'.$this->name;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SubSeries
     */
    public function setName(string $name): SubSeries
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Series
     */
    public function getSeries(): Series
    {
        return $this->series;
    }

    /**
     * @param Series $series
     * @return SubSeries
     */
    public function setSeries(Series $series): SubSeries
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return Student[]|Collection
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     * @param Student[]|Collection $students
     * @return SubSeries
     */
    public function setStudents($students)
    {
        $this->students = $students;
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
     * @return SubSeries
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
            'name' => $this->name,
            'series'=>$this->series
        );
    }

    /**
     * @return string
     */
    public function getType()
    {
        return ParticipantType::SUB_SERIES;
    }
}