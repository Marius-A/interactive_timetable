<?php

namespace AppBundle\Model\NodeEntity;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 * Class Staff
 * @package AppBundle\Model\NodeEntity
 *
 * @OGM\Node(label="Staff")
 */
abstract class Staff extends Person
{
    /**
     * @OGM\Property(type="string")
     * @var string
     */
    protected $email;

    /**
     * Staff constructor.
     * @param string $name
     * @param string $surname
     * @param string $email
     */
    public function __construct(string $name, string $surname, string $email)
    {
        parent::__construct($name, $surname);
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Staff
     */
    public function setEmail(string $email): Staff
    {
        $this->email = $email;
        return $this;
    }
}