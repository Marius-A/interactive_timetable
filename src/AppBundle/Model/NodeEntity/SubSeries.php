<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class SubSeries
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="SubSeries")
 */
class SubSeries extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="HAVE_SUB_SERIES", direction="INCOMING", collection=false, mappedBy="subSeries")
     *
     * @var Series
     */
    protected $series;

    /**
     * @var Calendar
     *
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="subSeries")
     */
    protected $calendar;

    /**
     * SubSeries constructor.
     * @param string $name
     * @param Series $series
     */
    public function __construct($name, Series $series)
    {
        $this->name = $name;
        $this->series = $series;
        $this->calendar = new Calendar($series->getName().'-'.$name.'-calendar');
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
     * @return Calendar
     */
    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     * @return SubSeries
     */
    public function setCalendar(Calendar $calendar): SubSeries
    {
        $this->calendar = $calendar;
        return $this;
    }
}