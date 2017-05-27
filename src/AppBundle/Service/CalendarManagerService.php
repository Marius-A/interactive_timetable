<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Calendar;
use AppBundle\Model\NodeEntity\Course;
use AppBundle\Model\NodeEntity\Event;
use AppBundle\Service\Traits\EntityManagerTrait;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class CalendarManagerService
 * @package AppBundle\Service
 */
class CalendarManagerService
{
    use EntityManagerTrait;

    const SERVICE_NAME = 'app.calendar_manager.service';

    /**
     * Create new Calendar object and save it into database
     */
    public function createNew(){
        $calendar = new Calendar();

        $this->getEntityManager()->persist($calendar);
        $this->getEntityManager()->flush();

        return $calendar;
    }

    /**
     * @param Event | Course  $event
     * @param Calendar        $calendar
     */
    public function addEvent($event, $calendar){
        //TODO Check for overlaps
        $calendar->getEvents()->add($event);
    }

    /**
     * @param Calendar            $calendar
     * @param Event[]| Collection $eventList
     */
    public function addEventList($calendar, $eventList){
        $iterator = $eventList->getIterator();

        while (($event = $iterator->next()) !== false) {
            $this->addEvent($event, $calendar);
        }
    }

    /**
     * @param Event | Course  $event
     * @param Calendar        $calendar
     */
    public function removeEvent($event, $calendar){
        $calendar->getEvents()->removeElement($event);
    }

    /**
     * @param Calendar        $calendar
     */
    public function removeAllEvents($calendar){
        foreach ($calendar->getEvents() as $event){
            $calendar->getEvents()->removeElement($event);
        }
    }
}