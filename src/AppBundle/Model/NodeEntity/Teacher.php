<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Teacher
 * @package AppBundle\Model\NodeEntity
 * @OGM\Node(label="Teacher")
 */
class Teacher extends Staff
{
    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="TEACHES", direction="OUTGOING", collection=true, mappedBy="teacher", targetEntity="EvaluationActivity")
     * @var EvaluationActivity[] | Collection
     */
    private $evaluationActivities;

    /**
     * @Serializer\Exclude()
     * @OGM\Relationship(type="TEACHES", direction="OUTGOING", collection=true, mappedBy="teacher", targetEntity="TeachingActivity")
     * @var TeachingActivity[] | Collection
     */
    private $teachingActivities;

    /**
     * Teacher constructor.
     * @param string $name
     * @param string $surname
     * @param string $email
     */
    public function __construct(string $name, string $surname, string $email)
    {
        parent::__construct($name, $surname, $email);
        $this->email = $email;
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
     */
    public function setEvaluationActivities($evaluationActivities)
    {
        $this->evaluationActivities = $evaluationActivities;
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
     */
    public function setTeachingActivities($teachingActivities)
    {
        $this->teachingActivities = $teachingActivities;
    }


}