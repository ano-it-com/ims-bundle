<?php

namespace ANOITCOM\IMSBundle\Entity\Location;

use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Location\LocationRepository;

/**
 * @ORM\Entity(repositoryClass=LocationRepository::class)
 * @ORM\Table(name="ims_locations")
 */
class Location
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @ORM\ManyToOne(targetEntity=Location::class)
     */
    private $parent;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getLevel(): int
    {
        return $this->level;
    }


    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }


    public function getParent(): ?self
    {
        return $this->parent;
    }


    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

}
