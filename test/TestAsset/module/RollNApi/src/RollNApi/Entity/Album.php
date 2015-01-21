<?php

namespace RollNApi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 */
class Album
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userAlbum;

    /**
     * @var \RollNApi\Entity\Artist
     */
    private $artist;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userAlbum = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Album
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add userAlbum
     *
     * @param \RollNApi\Entity\UserAlbum $userAlbum
     * @return Album
     */
    public function addUserAlbum(\RollNApi\Entity\UserAlbum $userAlbum)
    {
        $this->userAlbum[] = $userAlbum;

        return $this;
    }

    /**
     * Remove userAlbum
     *
     * @param \RollNApi\Entity\UserAlbum $userAlbum
     */
    public function removeUserAlbum(\RollNApi\Entity\UserAlbum $userAlbum)
    {
        $this->userAlbum->removeElement($userAlbum);
    }

    /**
     * Get userAlbum
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserAlbum()
    {
        return $this->userAlbum;
    }

    /**
     * Set artist
     *
     * @param \RollNApi\Entity\Artist $artist
     * @return Album
     */
    public function setArtist(\RollNApi\Entity\Artist $artist = null)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return \RollNApi\Entity\Artist 
     */
    public function getArtist()
    {
        return $this->artist;
    }
}
