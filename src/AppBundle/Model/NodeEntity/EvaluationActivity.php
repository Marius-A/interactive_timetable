<?php


namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class EvaluationActivity
 * @package AppBundle\Model\NodeEntity
 */
class EvaluationActivity extends Activity
{
    //TODO ADD ANNOTATIONS
    /** @var  \DateTime */
    private $date;
    /** @var  int */
    private $duration;

    /** @var  Teacher[] | Collection */
    private $teachers;

    /** @var Subject */
    private $subject;

    /** @var  Participant[] | Collection */
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
    }

}