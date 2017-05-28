<?php
namespace AppBundle\Model\NodeEntity;

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
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $description;

    /**
     * @var int
     *
     * @OGM\Property(type="int")
     */
    protected $yearOfStudy;

    /**
     * @var Specialization
     *
     * @OGM\Relationship(type="BELONGS_TO", direction="OUTGOING", collection=false, mappedBy="subjects", targetEntity="Specialization")
     */
    protected $specialization;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Subject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Subject
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Specialization
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * @param Specialization $specialization
     * @return Subject
     */
    public function setSpecialization($specialization)
    {
        $this->specialization = $specialization;
        return $this;
    }

    /**
     * @return int
     */
    public function getYearOfStudy()
    {
        return $this->yearOfStudy;
    }

    /**
     * @param int $yearOfStudy
     * @return Subject
     */
    public function setYearOfStudy(int $yearOfStudy)
    {
        $this->yearOfStudy = $yearOfStudy;
        return $this;
    }
}
