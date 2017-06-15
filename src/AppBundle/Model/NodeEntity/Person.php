<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Person
 * @package AppBundle\Model
 *
 * @OGM\Node(label="Person")
 */
abstract class Person extends BaseModel
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $name;

    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $surname;

    /**
     * Person constructor.
     * @param string $name
     * @param string $surname
     */
    public function __construct(string $name,string $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Person
     */
    public function setName(string $name): Person
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return Person
     */
    public function setSurname(string $surname): Person
    {
        $this->surname = $surname;
        return $this;
    }
}
