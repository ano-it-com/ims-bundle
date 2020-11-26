<?php

namespace ANOITCOM\IMSBundle\Entity\File;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\File\FileRepository;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 * @ORM\Table(name="ims_files", indexes={
 *     @ORM\Index(name="fileable_idx", columns={"owner_code", "owner_id", "deleted"})
 * })
 */
class File
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $ownerCode;

    /**
     * @ORM\Column(type="integer")
     */
    private $ownerId;

    /**
     * @ORM\Column(type="text")
     */
    private $path;

    /**
     * @ORM\Column(type="text")
     */
    private $originalName;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;


    public function __construct()
    {
        $this->deleted   = false;
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getOwnerCode(): string
    {
        return $this->ownerCode;
    }


    public function setOwnerCode(string $ownerCode): self
    {
        $this->ownerCode = $ownerCode;

        return $this;
    }


    public function getOwnerId(): int
    {
        return $this->ownerId;
    }


    public function setOwnerId(int $ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }


    public function getPath(): string
    {
        return $this->path;
    }


    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }


    public function getOriginalName(): string
    {
        return $this->originalName;
    }


    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }


    public function getSize(): int
    {
        return $this->size;
    }


    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }


    public function getDeleted(): bool
    {
        return $this->deleted;
    }


    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }


    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }


    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

}