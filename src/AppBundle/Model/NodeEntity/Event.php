<?php

namespace AppBundle\Model;


use AppBundle\Model\NodeEntity\Location;
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
     * @var  integer
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
     * @OGM\Property(type="int")
     * @var integer
     */
    protected $recurrenceInterval;

    /**
     * @OGM\Property(type="string")
     * @Enum({"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"})
     * @var string
     */
    protected $recurrenceDay;

    /**
     * @var Location
     */
    protected $location;
}