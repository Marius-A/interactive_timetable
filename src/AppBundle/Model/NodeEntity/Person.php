<?php

namespace AppBundle\Model;

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
     * @OGM\Property(type="string")
     * @var string
     */
    protected $email;

}
