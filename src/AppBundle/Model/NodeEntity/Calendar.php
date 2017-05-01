<?php

namespace AppBundle\Model\NodeEntity;


use AppBundle\Model\BaseModel;
use AppBundle\Model\Event;
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
     * @var  Event[] | Collection
     *
     * @OGM\Relationship(type="HAVE", direction="OUTGOING", collection=true, mappedBy="calendar")
     */
    protected $events;

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
}