<?php
namespace AppBundle\Model\NodeEntity;


use AppBundle\Model\Person;
use GraphAware\Neo4j\OGM\Common\Collection;


/**
 * Class Student
 * @package AppBundle\Model\NodeEntity
 */
class Student extends Person
{
    /**
     * @var integer
     */
    protected $yearOfStudy;

    /**
     * @var Series
     */
    protected $series;

    /**
     * @var Specialization
     */
    protected $specialization;

    /**
     * @var Course[] | Collection
     */
    protected $classes;
}