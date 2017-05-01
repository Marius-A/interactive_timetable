<?php

namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * Class Department
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Department")
 */
class Department extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $shortName;

    /**
     * @OGM\Property(type="string")
     *
     * @var string
     */
    protected $fullName;

    /**
     * @OGM\Relationship(type="HAVE", direction="BOTH", collection=false, mappedBy="faculty", targetEntity="Faculty")
     * @var Faculty
     */
    protected $faculty;

    /**
     * @OGM\Relationship(type="PART_OF", direction="OUTGOING", collection=true, mappedBy="department", targetEntity="Specialization")
     *
     * @var Specialization[] | Collection
     */
    protected $specializations;


}