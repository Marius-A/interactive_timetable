<?php

namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\Frequency;
use Doctrine\Common\Annotations\Annotation\Enum;

/**
 * Class Event
 * @package AppBundle\Model
 *
 * @OGM\Node(label="Event")
 */
class Event extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $description;

    /**
     * @Enum({"ACTIVE", "CANCELED"})
     * @OGM\Property(type="string")
     *
     * @var  string
     */
    protected $status;

    /**
     * @OGM\Property(type="int")
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @OGM\Property(type="int")
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @OGM\Property(type="int")
     * @var \DateTime
     */
    protected $repeatUntil;

    /**
     * @OGM\Property(type="string")
     * @Enum({"NO_REPEAT","REPEAT_DAILY","REPEAT_WEEKLY","REPEAT_MONTHLY"})
     * @see Frequency
     */
    protected $recurrenceFrequency;

    /**
     * every x days
     * @OGM\Property(type="int")
     * @var integer
     */
    protected $recurrenceInterval;

    /**
     * @OGM\Property(type="string")
     * @Enum({"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"})
     * @var string[]
     */
    protected $recurrenceDays;

    /**
     * @var Location
     */
    protected $location;

    /**
     * Event constructor.
     * @param string $name
     * @param string $description
     * @param int $status
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param \DateTime $repeatUntil
     * @param $recurrenceFrequency
     * @param int $recurrenceInterval
     * @param string $recurrenceDay
     * @param Location $location
     */
    public function __construct($name, $description, $status, \DateTime $startDate, \DateTime $endDate, Location $location, \DateTime $repeatUntil = null, $recurrenceFrequency = null, $recurrenceInterval = null, $recurrenceDay = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->repeatUntil = $repeatUntil;
        $this->recurrenceFrequency = $recurrenceFrequency;
        $this->recurrenceInterval = $recurrenceInterval;
        $this->recurrenceDay = $recurrenceDay;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Event
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRepeatUntil()
    {
        return $this->repeatUntil;
    }

    /**
     * @param \DateTime $repeatUntil
     * @return Event
     */
    public function setRepeatUntil($repeatUntil)
    {
        $this->repeatUntil = $repeatUntil;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurrenceFrequency()
    {
        return $this->recurrenceFrequency;
    }

    /**
     * @param mixed $recurrenceFrequency
     * @return Event
     */
    public function setRecurrenceFrequency($recurrenceFrequency)
    {
        $this->recurrenceFrequency = $recurrenceFrequency;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecurrenceInterval()
    {
        return $this->recurrenceInterval;
    }

    /**
     * @return \string[]
     */
    public function getRecurrenceDays()
    {
        return $this->recurrenceDays;
    }

    /**
     * @param \string[] $recurrenceDays
     * @return Event
     */
    public function setRecurrenceDays(array $recurrenceDays)
    {
        $this->recurrenceDays = $recurrenceDays;
        return $this;
    }


    /**
     * @param string $recurrenceDay
     * @return Event
     */
    public function setRecurrenceDay($recurrenceDay)
    {
        $this->recurrenceDay = $recurrenceDay;
        return $this;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     * @return Event
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }
}