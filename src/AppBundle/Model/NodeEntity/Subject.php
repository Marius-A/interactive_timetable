<?php
namespace AppBundle\Model\NodeEntity;

use AppBundle\Model\BaseModel;
use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Subject
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Subject")
 */
class Subject extends BaseModel
{
    /**
     * @var string
     *
     * OGM\Property(type="string")
     */
    protected $name;

    /**
     * @var string
     *
     * OGM\Property(type="string")
     */
    protected $description;

    /**
     * @var Department
     *
     * @OGM\Relationship(type="BELONGS", direction="INCOMING", collection=false, mappedBy="subjects", targetEntity="Specialization")
     */
    protected $specialization;

}
