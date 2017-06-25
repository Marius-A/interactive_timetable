<?php


namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Model\NodeEntity\Util\DayOfWeek;
use AppBundle\Model\NodeEntity\Util\WeekType;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class TeachingActivity
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="TeachingActivity")
 */
class TeachingActivity extends Activity
{
    /**
     * @OGM\Relationship(type="ON_SEMESTER", direction="OUTGOING", collection=false, mappedBy="teachingActivities", targetEntity="Semester")
     * @var  Semester
     */
    private $semester;

    /**
     * @OGM\Property(type="string")
     * @var  string | WeekType
     */
    private $weekType;

    /**
     * @OGM\Property(type="int")
     * @var  integer | DayOfWeek
     */
    private $day;

    /**
     * @OGM\Property(type="int")
     * @var  integer
     */
    private $hour;

    /**
     * @OGM\Property(type="int")
     * @var  integer
     */
    private $duration;

    /**
     * @OGM\Relationship(type="TEACHED_BY", direction="INCOMING", collection=false, mappedBy="teachingActivities", targetEntity="Teacher")
     * @var  Teacher
     */
    private $teacher;

    /**
     * @OGM\Relationship(type="LINKED_TO", direction="OUTGOING", collection=false, mappedBy="teachingActivities", targetEntity="Subject")
     * @var  Subject
     */
    private $subject;

    /**
     * @OGM\Relationship(type="PARTICIPATE", direction="INCOMING", collection=true, mappedBy="teachingActivities", targetEntity="Participant")
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
        Subject $subject
    )
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

    /**
     * @param Semester $semester
     * @return TeachingActivity
     */
    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    /**
     * @param WeekType|string $weekType
     * @return TeachingActivity
     */
    public function setWeekType($weekType)
    {
        $this->weekType = $weekType;
        return $this;
    }

    /**
     * @param DayOfWeek|int $day
     * @return TeachingActivity
     */
    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @param int $hour
     * @return TeachingActivity
     */
    public function setHour(int $hour): TeachingActivity
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * @param int $duration
     * @return TeachingActivity
     */
    public function setDuration(int $duration): TeachingActivity
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @param Teacher $teacher
     * @return TeachingActivity
     */
    public function setTeacher(Teacher $teacher): TeachingActivity
    {
        $this->teacher = $teacher;
        return $this;
    }

    /**
     * @param Subject $subject
     * @return TeachingActivity
     */
    public function setSubject(Subject $subject): TeachingActivity
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array(
            'semester' => $this->semester->getKey(),
            'weekType' => $this->weekType,
            'day' => $this->day,
            'hour' => $this->hour,
            'duration' => $this->duration,
            'type'=> $this->activityCategory,
            'location'=> $this->location->getFullName(),
            'teacher' => $this->teacher->getName() . ' ' . $this->teacher->getSurname(),
            'subject' => $this->subject->getFullName()
        );
    }
}