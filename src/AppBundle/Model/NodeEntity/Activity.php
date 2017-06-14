<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Activity
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Activity")
 */
abstract class Activity extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var  string
     * @see ActivityCategory
     */
    protected $activityCategory;
    /**
     * @OGM\Relationship(type="IN", direction="OUTGOING", collection=false, mappedBy="activities", targetEntity="Location")
     * @var  Location
     */
    protected $location;

    /**
     * Activity constructor.
     * @param ActivityCategory | string $activityCategory
     * @param Location $location
     */
    public function __construct($activityCategory, Location $location)
    {
        $this->activityCategory = $activityCategory;
        $this->location = $location;
    }

    /**
     * @return ActivityCategory | string
     */
    public function getActivityCategory()
    {
        return $this->activityCategory;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }
}