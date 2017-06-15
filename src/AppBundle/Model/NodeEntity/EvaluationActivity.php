<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class EvaluationActivity
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Activity")
 */
class EvaluationActivity extends Activity
{
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime", options={"format":"long_timestamp"})
     * @var  \DateTime
     */
    private $date;

    /**
     * @OGM\Property(type="int")
     * @var  int
     */
    private $duration;

    /**
     * @OGM\Relationship(type="ASSIST", direction="INCOMING", collection=false, targetEntity="Teacher")
     * @var  Teacher[] | Collection
     */
    private $assistants;

    /**
     * @OGM\Relationship(type="LINKED_TO", direction="INCOMING", mappedBy="evaluationActivities", collection=false, targetEntity="Subject")
     * @var Subject
     */
    private $subject;

    /**
     * @OGM\Relationship(type="PARTICIPATE", direction="INCOMING", collection=true, mappedBy="evaluationActivities", targetEntity="Participant")
     * @var  Participant[] | Collection
     */
    private $participants;


    /** @OGM\Label(name="Activity") */
    protected $isAnActivity = true;

    /**
     * TeachingActivity constructor.
     *
     * @param Location $location
     * @param ActivityCategory | string $activityCategory
     * @param \DateTime $date
     * @param int $duration
     * @param Subject $subject
     * @internal param Teacher $teacher
     */
    public function __construct(
        Location $location,
        $activityCategory,
        \DateTime $date,
        int $duration,
        Subject $subject)
    {
        parent::__construct($activityCategory, $location);
        $this->subject = $subject;
        $this->date = $date;
        $this->duration = $duration;
        $this->participants = new Collection();
        $this->assistants = new Collection();
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return EvaluationActivity
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return EvaluationActivity
     */
    public function setDuration(int $duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return Teacher[]|Collection
     */
    public function getAssistants()
    {
        return $this->assistants;
    }

    /**
     * @param Teacher[]|Collection $assistants
     * @return EvaluationActivity
     */
    public function setAssistants($assistants)
    {
        $this->assistants = $assistants;
        return $this;
    }

    /**
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param Subject $subject
     * @return EvaluationActivity
     */
    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return Participant[]|Collection
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param Participant[]|Collection $participants
     * @return EvaluationActivity
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return array(
            'date'=>json_encode($this->date),
            'duration'=>$this->duration
        );
    }
}