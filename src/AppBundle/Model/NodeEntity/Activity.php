<?php


namespace AppBundle\Model\NodeEntity;

/**
 * Class Activity
 * @package AppBundle\Model\NodeEntity
 */
class Activity extends BaseModel
{
    /** @var  string | ActivityCategory */
    private $activityCategory;
    /** @var  Location */
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