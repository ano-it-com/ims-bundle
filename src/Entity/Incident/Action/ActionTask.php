<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Action;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Domain\File\FileOwnerInterface;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionTaskRepository;

/**
 * @ORM\Entity(repositoryClass=ActionTaskRepository::class)
 * @ORM\Table(name="ims_action_tasks")
 */
class ActionTask implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Action::class, inversedBy="actionTasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity=ActionTaskType::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=ActionTaskStatus::class, cascade={"persist", "remove"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=ActionTaskStatus::class, mappedBy="actionTask")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $statuses;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb": true})
     */
    private $inputData = [];

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb": true})
     */
    private $reportData = [];

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
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;


    public function __construct()
    {
        $this->deleted   = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->statuses  = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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


    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }


    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    public function getUpdatedBy(): User
    {
        return $this->updatedBy;
    }


    public function setUpdatedBy(User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

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


    public function getStatus(): ?ActionTaskStatus
    {
        return $this->status;
    }


    public function setStatus(?ActionTaskStatus $status): self
    {
        $this->status = $status;

        return $this;
    }


    /**
     * @return Collection|ActionTaskStatus []
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }


    public function addStatus(ActionTaskStatus $status): self
    {
        if ( ! $this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setActionTask($this);
        }

        return $this;
    }


    public function removeStatus(ActionTaskStatus $status): self
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getActionTask() === $this) {
                $status->setActionTask(null);
            }
        }

        return $this;
    }


    public function getInputData(): ?array
    {
        return $this->inputData;
    }


    public function setInputData(?array $inputData): self
    {
        $this->inputData = $inputData;

        return $this;
    }


    public function getReportData(): ?array
    {
        return $this->reportData;
    }


    public function setReportData(?array $reportData): self
    {
        $this->reportData = $reportData;

        return $this;
    }


    public function getType(): ActionTaskType
    {
        return $this->type;
    }


    public function setType(ActionTaskType $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getOwnerCode(): string
    {
        return 'action_task';
    }
}