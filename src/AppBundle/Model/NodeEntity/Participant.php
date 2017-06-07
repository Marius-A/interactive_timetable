<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Participant
 * @package AppBundle\Model\NodeEntity
 */
abstract class Participant extends BaseModel
{
    /**
     * @OGM\Relationship(type="PARTICIPATE", direction="OUTGOING", collection=true, mappedBy="participants", targetEntity="EvaluationActivity")
     * @var EvaluationActivity[] | Collection
     */
    private $evaluationActivities;

    /**
     * @OGM\Relationship(type="PARTICIPATE", direction="OUTGOING", collection=true, mappedBy="participants", targetEntity="TeachingActivity")
     * @var TeachingActivity[] | Collection
     */
    private $teachingActivities;

    /**
     * Participant constructor.
     */
    public function __construct()
    {
        $this->evaluationActivities = new Collection();
        $this->teachingActivities = new Collection();
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
     * @return Participant
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
     * @return Participant
     */
    public function setTeachingActivities($teachingActivities)
    {
        $this->teachingActivities = $teachingActivities;
        return $this;
    }
}