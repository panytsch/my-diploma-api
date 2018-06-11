<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Board
 *
 * @ORM\Table(name="board")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BoardRepository")
 */
class Board
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", length=255, type="string")
     */
    private $title;

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ArrayCollection $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Stick", mappedBy="board")
     */
    private $stick;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="boards")
     */
    private $user;


    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Board
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Board
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->stick = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add stick
     *
     * @param \AppBundle\Entity\Stick $stick
     *
     * @return Board
     */
    public function addStick(\AppBundle\Entity\Stick $stick)
    {
        $this->stick[] = $stick;

        return $this;
    }

    /**
     * Remove stick
     *
     * @param \AppBundle\Entity\Stick $stick
     */
    public function removeStick(\AppBundle\Entity\Stick $stick)
    {
        $this->stick->removeElement($stick);
    }

    /**
     * Get stick
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStick()
    {
        return $this->stick;
    }
}
