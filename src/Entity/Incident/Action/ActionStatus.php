<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Action;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionStatusRepository;

/**
 * @ORM\Entity(repositoryClass=ActionStatusRepository::class)
 * @ORM\Table(name="ims_action_statuses")
 */
class ActionStatus
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
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity=Action::class, inversedBy="statuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $responsibleGroup;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $responsibleUser;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
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


    public function getAction(): Action
    {
        return $this->action;
    }


    public function setAction(Action $action): self
    {
        $this->action = $action;

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


    public function getResponsibleUser(): ?User
    {
        return $this->responsibleUser;
    }


    public function setResponsibleUser(?User $responsibleUser): self
    {
        $this->responsibleUser = $responsibleUser;

        return $this;
    }


    public function getResponsibleGroup(): UserGroup
    {
        return $this->responsibleGroup;
    }


    public function setResponsibleGroup(UserGroup $responsibleGroup): self
    {
        $this->responsibleGroup = $responsibleGroup;

        return $this;
    }
}