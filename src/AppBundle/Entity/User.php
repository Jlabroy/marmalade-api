<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="first_name", type="string", length=100)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $password;

    /**
     * User constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->firstName = $data['first_name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

}
