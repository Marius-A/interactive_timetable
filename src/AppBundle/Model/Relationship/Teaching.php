<?php

namespace AppBundle\Model\Relationship;



use AppBundle\Model\BaseModel;
use AppBundle\Model\NodeEntity\Course;
use AppBundle\Model\NodeEntity\Teacher;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Teaching
 * @package AppBundle\Model\Relationship
 * @OGM\RelationshipEntity()
 */
class Teaching extends BaseModel
{
    /**
     * @var Teacher
     *
     * @OGM\StartNode(targetEntity="Teacher")
     */
    protected $teacher;

    /**
     * @var Course
     *
     * @OGM\EndNode(targetEntity="Course")
     */
    protected $class;

    /**
     * @return Teacher
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * @param Teacher $teacher
     * @return Teaching
     */
    public function setTeacher(Teacher $teacher)
    {
        $this->teacher = $teacher;
        return $this;
    }

    /**
     * @return Course
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param Course $class
     * @return Teaching
     */
    public function setClass(Course $class)
    {
        $this->class = $class;
        return $this;
    }
}