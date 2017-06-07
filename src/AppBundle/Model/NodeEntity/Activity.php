<?php


namespace AppBundle\Model\NodeEntity;

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
    private $activityCategory;
    /**
     * @OGM\Relationship(type="LOCATED_IN", direction="INCOMING", collection=false, mappedBy="activity", targetEntity="Location")
     * @var  Location
     */
    private $location;

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
    public function getActivityCategory(): ActivityCategory
    {
        return $this->activityCategory;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }
}