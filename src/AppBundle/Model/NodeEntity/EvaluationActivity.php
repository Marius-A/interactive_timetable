<?php


namespace AppBundle\Model\NodeEntity;

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
     * @OGM\Relationship(type="??????", direction="INCOMING",mappedBy="evaluationActivities" collection=false, targetEntity="Subject")
     * @var Subject
     */
    private $subject;

    /**
     *
     * @var  Participant[] | Collection
     */
    private $participants;

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

}