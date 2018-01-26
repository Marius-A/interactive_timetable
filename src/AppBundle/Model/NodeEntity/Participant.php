<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\ParticipantType;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Participant
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Participant")
 */
abstract class Participant extends BaseModel implements \JsonSerializable
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $identifier;

    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="PARTICIPATE", direction="OUTGOING", collection=true, mappedBy="participants", targetEntity="EvaluationActivity")
     * @var EvaluationActivity[] | Collection
     */
    protected $evaluationActivities;

    /**
     *
     * @OGM\Relationship(type="PARTICIPATE", direction="OUTGOING", collection=true, mappedBy="participants", targetEntity="TeachingActivity")
     * @var TeachingActivity[] | Collection
     */
    protected $teachingActivities;

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
     * @return ParticipantType
     */
    public abstract function getType();

    /**
     * @param TeachingActivity[]|Collection $teachingActivities
     * @return Participant
     */
    public function setTeachingActivities($teachingActivities)
    {
        $this->teachingActivities = $teachingActivities;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Participant
     */
    public function setIdentifier(string $identifier): Participant
    {
        $this->identifier = $identifier;
        return $this;
    }

    function jsonSerialize()
    {
        return array(
            'identifier' => $this->identifier,
            'evaluationActivities'=>$this->evaluationActivities->toArray(),
            'teachingActivities'=>$this->teachingActivities->toArray()
        );
    }
}