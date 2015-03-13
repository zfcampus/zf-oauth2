<?php

namespace RollNApi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Artist
 */
class Artist
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
    private $album;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->album = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Artist
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
     * Add album
     *
     * @param \RollNApi\Entity\Album $album
     * @return Artist
     */
    public function addAlbum(\RollNApi\Entity\Album $album)
    {
        $this->album[] = $album;

        return $this;
    }

    /**
     * Remove album
     *
     * @param \RollNApi\Entity\Album $album
     */
    public function removeAlbum(\RollNApi\Entity\Album $album)
    {
        $this->album->removeElement($album);
    }

    /**
     * Get album
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlbum()
    {
        return $this->album;
    }
}
