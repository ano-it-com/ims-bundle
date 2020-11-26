<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Action;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Domain\File\FileOwnerInterface;
use ANOITCOM\IMSBundle\Entity\Incident\Comment\Comment;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionRepository;

/**
 * @ORM\Entity(repositoryClass=ActionRepository::class)
 * @ORM\Table(name="ims_actions")
 */
class Action implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @ORM\OneToOne(targetEntity=ActionStatus::class, cascade={"persist", "remove"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=ActionStatus::class, mappedBy="action")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity=ActionTask::class, mappedBy="action")
     */
    private $actionTasks;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="action")
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity=Incident::class, inversedBy="actions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incident;

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
        $this->deleted     = false;
        $this->createdAt   = new \DateTimeImmutable();
        $this->updatedAt   = new \DateTimeImmutable();
        $this->statuses    = new ArrayCollection();
        $this->actionTasks = new ArrayCollection();
        $this->comments    = new ArrayCollection();
    }


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


    public function setDescription(?string $description): self
    {
        $this->description = $description;

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


    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }


    public function addComment(Comment $comment): self
    {
        if ( ! $this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAction($this);
        }

        return $this;
    }


    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAction() === $this) {
                $comment->setAction(null);
            }
        }

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


    public function getStatus(): ?ActionStatus
    {
        return $this->status;
    }


    public function getStatusCode(): ?string
    {
        $status = $this->getStatus();
        if ( ! $status) {
            return null;
        }

        return $status->getCode();
    }


    public function setStatus(?ActionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getIncident(): Incident
    {
        return $this->incident;
    }


    public function setIncident(Incident $incident): self
    {
        $this->incident = $incident;

        return $this;
    }


    /**
     * @return Collection|ActionStatus []
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }


    public function addStatus(ActionStatus $status): self
    {
        if ( ! $this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setAction($this);
        }

        return $this;
    }


    public function removeStatus(ActionStatus $status): self
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getAction() === $this) {
                $status->setAction(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|ActionTask []
     */
    public function getActionTasks(): Collection
    {
        return $this->actionTasks;
    }


    public function addActionTask(ActionTask $status): self
    {
        if ( ! $this->actionTasks->contains($status)) {
            $this->actionTasks[] = $status;
            $status->setAction($this);
        }

        return $this;
    }


    public function removeActionTask(ActionTask $status): self
    {
        if ($this->actionTasks->contains($status)) {
            $this->actionTasks->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getAction() === $this) {
                $status->setAction(null);
            }
        }

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


    public function getActionTaskById(int $actionTaskId): ?ActionTask
    {
        foreach ($this->getActionTasks() as $actionTask) {
            if ($actionTask->getId() === $actionTaskId) {
                return $actionTask;
            }
        }

        return null;
    }


    public function getOwnerCode(): string
    {
        return 'action';
    }
}