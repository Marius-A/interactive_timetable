<?php


namespace AppBundle\Model\NodeEntity;

/**
 * Class AcademicYear
 * @package AppBundle\Model\NodeEntity
 */
class AcademicYear
{
    /** @var  string */
    private $name;

    /**
     * AcademicYear constructor.
     * @example 2016-2017
     * @param  string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}