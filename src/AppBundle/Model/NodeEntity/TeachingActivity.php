<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\DayOfWeek;
use AppBundle\Model\NodeEntity\Util\WeekType;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class TeachingActivity
 * @package AppBundle\Model\NodeEntity
 */
class TeachingActivity extends Activity
{
    /** @var  Semester */
    private $semester;
    /** @var  string | WeekType */
    private $weekType;
    /** @var  integer | DayOfWeek */
    private $day;
    /** @var  integer */
    private $hour;
    /** @var  integer */
    private $duration;

    /** @var  Teacher */
    private $teacher;
    /** @var  Subject */
    private $subject;

    /** @var  Participant[] | Collection */
    private $participants;

    /**
     * TeachingActivity constructor.
     *
     * @param Location $location
     * @param ActivityCategory | string $activityCategory
     * @param Semester $semester
     * @param WeekType|string $weekType
     * @param int $day
     * @param int $hour
     * @param int $duration
     * @param Teacher $teacher
     * @param Subject $subject
     */
    public function __construct(
        Location $location,
        $activityCategory,
        Semester $semester,
        string $weekType,
        int $day,
        int $hour,
        int $duration,
        Teacher $teacher,
        Subject $subject)
    {
        parent::__construct($activityCategory, $location);
        $this->semester = $semester;
        $this->weekType = $weekType;
        $this->day = $day;
        $this->hour = $hour;
        $this->duration = $duration;
        $this->teacher = $teacher;
        $this->subject = $subject;
        $this->participants = new Collection();
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
     * @return TeachingActivity
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
        return $this;
    }



    /**
     * @return Semester
     */
    public function getSemester(): Semester
    {
        return $this->semester;
    }

    /**
     * @return WeekType|string
     */
    public function getWeekType()
    {
        return $this->weekType;
    }

    /**
     * @return int
     */
    public function getHour(): int
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return DayOfWeek|int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return Teacher
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    /**
     * @return Subject
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }
}