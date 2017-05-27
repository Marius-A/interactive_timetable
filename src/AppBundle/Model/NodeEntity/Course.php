<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Course
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Course")
 */
class Course extends Event
{
    /**
     * OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Relationship(type="HAVE", collection=false, mappedBy="courses", targetEntity="Subject")
     * @var Subject
     */
    protected $subject;

    /**
     * OGM\Property(type="string")
     * @var string
     * @Enum({"COURSE","LABORATORY","SEMINARY","PROJECT"})
     */
    protected $type;

    /**
     * @OGM\Relationship(relationshipEntity="Teaching", direction="INCOMING", collection=false, mappedBy="courses", targetEntity="Teacher")
     * @var Teacher
     */
    protected $teacher;

    /**
     * Course constructor.
     * @param string $name
     * @param Subject $subject
     * @param string $type
     * @param $teacher
     */
    public function __construct($name, Subject $subject, $type, $teacher)
    {
        $this->name = $name;
        $this->subject = $subject;
        $this->type = $type;
        $this->teacher = $teacher;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * @param mixed $teacher
     */
    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;
    }
}