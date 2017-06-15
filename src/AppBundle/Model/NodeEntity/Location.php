<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Location
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Location")
 */
class Location extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $shortName;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $fullName;

    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="IN", direction="INCOMING", collection=true, mappedBy="location", targetEntity="Activity")
     * @var Collection | Activity[]
     */
    protected $activities;

    /**
     * Location constructor.
     * @param string $name
     * @param string|null $fullName
     */
    public function __construct(string $name, string $fullName = null)
    {
        $this->shortName = $name;
        $this->fullName = $fullName;
        $this->activities = new Collection();
    }


    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Location
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     * @return Location
     */
    public function setFullName(string $fullName): Location
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * @param Collection $activities
     * @return Location
     */
    public function setActivities(Collection $activities)
    {
        $this->activities = $activities;
        return $this;
    }


}