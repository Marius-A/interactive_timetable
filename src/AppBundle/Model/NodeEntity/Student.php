<?php
namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Common\Collection;


/**
 * Class Student
 * @package AppBundle\Model\NodeEntity
 */
class Student extends Person
{
    /**
     * OGM\Property(type="int")
     * @var integer
     */
    private $yearOfStudy;

    /**
     * @OGM\Relationship(type="PART_OF", collection=false, mappedBy="students", targetEntity="SubSeries")
     * @var SubSeries
     */
    protected $subSeries;

    /**
     * @var Course[] | Collection
     */
    protected $classes;
}