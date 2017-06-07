<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

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
    protected $name;

    /**
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $description;

    /**
     * @OGM\Relationship(type="HAVE_SUBJECT", direction="OUTGOING", collection=true, mappedBy="subject", targetEntity="EvaluationActivities")
     * @var EvaluationActivity[] | Collection
     */
    protected $evaluationActivities;

    /**
     * @OGM\Relationship(type="HAVE_SUBJECT", direction="OUTGOING", collection=true, mappedBy="subject", targetEntity="TeachingActivity")
     * @var TeachingActivity[] | Collection
     */
    protected $teachingActivities;

    /**
     * Subject constructor.
     * @param string $name
     * @param string $description
     */
    public function __construct(string $name,string $description)
    {
        $this->name = $name;
        $this->description = $description;

        $this->evaluationActivities = new Collection();
        $this->teachingActivities = new Collection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
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
