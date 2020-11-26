<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Category;

use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Incident\Category\CategoryRepository;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(name="ims_categories")
 */
class Category
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
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


    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function setDescription(string $description): self
    {
        $this->description = $description;

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