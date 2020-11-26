<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Action;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionTaskStatusRepository;

/**
 * @ORM\Entity(repositoryClass=ActionTaskStatusRepository::class)
 * @ORM\Table(name="ims_action_task_statuses")
 */
class ActionTaskStatus
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
     * @ORM\ManyToOne(targetEntity=ActionTask::class, inversedBy="statuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionTask;


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


    public function getActionTask(): ActionTask
    {
        return $this->actionTask;
    }


    public function setActionTask(ActionTask $actionTask): self
    {
        $this->actionTask = $actionTask;

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

}