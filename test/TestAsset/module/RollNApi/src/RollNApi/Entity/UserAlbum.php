<?php

namespace RollNApi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAlbum
 */
class UserAlbum
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \RollNApi\Entity\User
     */
    private $user;

    /**
     * @var \RollNApi\Entity\Album
     */
    private $album;


    /**
     * Set description
     *
     * @param string $description
     * @return UserAlbum
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set user
     *
     * @param \RollNApi\Entity\User $user
     * @return UserAlbum
     */
    public function setUser(\RollNApi\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \RollNApi\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set album
     *
     * @param \RollNApi\Entity\Album $album
     * @return UserAlbum
     */
    public function setAlbum(\RollNApi\Entity\Album $album = null)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * Get album
     *
     * @return \RollNApi\Entity\Album 
     */
    public function getAlbum()
    {
        return $this->album;
    }
}
