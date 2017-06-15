<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Subject
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Subject")
 */
class Subject extends BaseModel
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
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $description;

    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="HAVE_SUBJECT", direction="INCOMING", collection=true, mappedBy="subject", targetEntity="EvaluationActivity")
     * @var EvaluationActivity[] | Collection
     */
    protected $evaluationActivities;

    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="HAVE_SUBJECT", direction="INCOMING", collection=true, mappedBy="subject", targetEntity="TeachingActivity")
     * @var TeachingActivity[] | Collection
     */
    protected $teachingActivities;

    /**
     * Subject constructor.
     * @param string $shortName
     * @param string $fullName
     * @param string $description
     * @internal param string $name
     */
    public function __construct(string $shortName, string  $fullName, string $description)
    {
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->description = $description;

        $this->evaluationActivities = new Collection();
        $this->teachingActivities = new Collection();
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return Subject
     */
    public function setShortName(string $shortName): Subject
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
     * @return Subject
     */
    public function setFullName(string $fullName): Subject
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Subject
     */
    public function setDescription(string $description): Subject
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return EvaluationActivity[]|Collection
     */
    public function getEvaluationActivities()
    {
        return $this->evaluationActivities;
    }

    /**
     * @param EvaluationActivity[]|Collection $evaluationActivities
     * @return Subject
     */
    public function setEvaluationActivities($evaluationActivities)
    {
        $this->evaluationActivities = $evaluationActivities;
        return $this;
    }

    /**
     * @return TeachingActivity[]|Collection
     */
    public function getTeachingActivities()
    {
        return $this->teachingActivities;
    }

    /**
     * @param TeachingActivity[]|Collection $teachingActivities
     * @return Subject
     */
    public function setTeachingActivities($teachingActivities)
    {
        $this->teachingActivities = $teachingActivities;
        return $this;
    }
}
