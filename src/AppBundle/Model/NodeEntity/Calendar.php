<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Calendar
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Calendar")
 */
class Calendar extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="HAVE", direction="OUTGOING", collection=true, mappedBy="calendar")
     *
     * @var  Event[] | Collection
     */
    protected $events;

    /**
     * Calendar constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->events = new Collection();
    }


    /**
     * @return Event[]|Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event[]|Collection $events
     * @return Calendar
     */
    public function setEvents($events)
    {
        $this->events = $events;
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
     * @param string $name
     * @return Calendar
     */
    public function setName(string $name): Calendar
    {
        $this->name = $name;
        return $this;
    }
}