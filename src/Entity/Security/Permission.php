<?php

namespace ANOITCOM\IMSBundle\Entity\Security;

use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Security\PermissionRepository;

/**
 * @ORM\Entity(repositoryClass=PermissionRepository::class)
 * @ORM\Table(name="ims_permissions")
 */
class Permission
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
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $restrictionType;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getCode(): string
    {
        return $this->code;
    }


    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }


    public function getRestrictionType(): ?string
    {
        return $this->restrictionType;
    }


    public function setRestrictionType(?string $restrictionType): self
    {
        $this->restrictionType = $restrictionType;

        return $this;
    }

}