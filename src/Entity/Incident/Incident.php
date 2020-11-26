<?php

namespace ANOITCOM\IMSBundle\Entity\Incident;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Domain\File\FileOwnerInterface;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Category\Category;
use ANOITCOM\IMSBundle\Entity\Incident\Comment\Comment;
use ANOITCOM\IMSBundle\Entity\Location\Location;
use ANOITCOM\IMSBundle\Repository\Incident\IncidentRepository;

/**
 * @ORM\Entity(repositoryClass=IncidentRepository::class)
 * @ORM\Table(name="ims_incidents")
 */
class Incident implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb": true})
     */
    private $source = [];

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $coverage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $spread;

    /**
     * @ORM\ManyToMany(targetEntity=Location::class)
     * @ORM\JoinTable(
     *  name="ims_incident_locations",
     *  joinColumns={
     *      @ORM\JoinColumn(name="incident_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     *  }
     * )
     */
    private $locations;

    /**
     * @ORM\ManyToMany(targetEntity=UserGroup::class)
     * @ORM\JoinTable(
     *  name="ims_incident_groups",
     *  joinColumns={
     *      @ORM\JoinColumn(name="incident_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *  }
     * )
     */
    private $responsibleGroups;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class)
     * @ORM\JoinTable(
     *  name="ims_incident_categories",
     *  joinColumns={
     *      @ORM\JoinColumn(name="incident_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *  }
     * )
     */
    private $categories;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $importance;

    /**
     * @ORM\OneToOne(targetEntity=IncidentStatus::class, cascade={"persist", "remove"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Incident::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $repeatedIncident;

    /**
     * @ORM\OneToMany(targetEntity=IncidentStatus::class, mappedBy="incident")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity=Action::class, mappedBy="incident")
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="incident")
     */
    private $comments;

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
        $this->deleted           = false;
        $this->createdAt         = new \DateTimeImmutable();
        $this->updatedAt         = new \DateTimeImmutable();
        $this->statuses          = new ArrayCollection();
        $this->actions           = new ArrayCollection();
        $this->comments          = new ArrayCollection();
        $this->locations         = new ArrayCollection();
        $this->responsibleGroups = new ArrayCollection();
        $this->categories        = new ArrayCollection();
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


    public function getSource(): ?array
    {
        return $this->source;
    }


    public function setSource(?array $source): self
    {
        $this->source = $source;

        return $this;
    }


    public function getCoverage(): ?float
    {
        return $this->coverage;
    }


    public function setCoverage(?float $coverage): self
    {
        $this->coverage = $coverage;

        return $this;
    }


    public function getSpread(): ?int
    {
        return $this->spread;
    }


    public function setSpread(?int $spread): self
    {
        $this->spread = $spread;

        return $this;
    }


    public function getImportance(): ?int
    {
        return $this->importance;
    }


    public function setImportance(?int $importance): self
    {
        $this->importance = $importance;

        return $this;
    }


    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }


    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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


    public function getStatus(): ?IncidentStatus
    {
        return $this->status;
    }


    public function setStatus(?IncidentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }


    /**
     * @return Collection|IncidentStatus[]
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }


    public function addStatus(IncidentStatus $status): self
    {
        if ( ! $this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setIncident($this);
        }

        return $this;
    }


    public function removeStatus(IncidentStatus $status): self
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            // set the owning side to null (unless already changed)
            if ($status->getIncident() === $this) {
                $status->setIncident(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }


    public function addAction(Action $action): self
    {
        if ( ! $this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setIncident($this);
        }

        return $this;
    }


    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getIncident() === $this) {
                $action->setIncident(null);
            }
        }

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
            $comment->setIncident($this);
        }

        return $this;
    }


    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getIncident() === $this) {
                $comment->setIncident(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }


    public function addLocation(Location $location): self
    {
        if ( ! $this->locations->contains($location)) {
            $this->locations[] = $location;
        }

        return $this;
    }


    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
        }

        return $this;
    }


    /**
     * @return Collection|UserGroup[]
     */
    public function getResponsibleGroups(): Collection
    {
        return $this->responsibleGroups;
    }


    public function addResponsibleGroup(UserGroup $responsibleGroup): self
    {
        if ( ! $this->responsibleGroups->contains($responsibleGroup)) {
            $this->responsibleGroups[] = $responsibleGroup;
        }

        return $this;
    }


    public function removeResponsibleGroup(UserGroup $responsibleGroup): self
    {
        if ($this->responsibleGroups->contains($responsibleGroup)) {
            $this->responsibleGroups->removeElement($responsibleGroup);
        }

        return $this;
    }


    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }


    public function addCategory(Category $location): self
    {
        if ( ! $this->categories->contains($location)) {
            $this->categories[] = $location;
        }

        return $this;
    }


    public function removeCategory(Category $location): self
    {
        if ($this->categories->contains($location)) {
            $this->categories->removeElement($location);
        }

        return $this;
    }


    public function getActionBy(int $actionId): ?Action
    {
        foreach ($this->getActions() as $action) {
            if ($action->getId() === $actionId) {
                return $action;
            }
        }

        return null;
    }


    public function getStatusCode(): ?string
    {
        $status = $this->getStatus();
        if ( ! $status) {
            return null;
        }

        return $status->getCode();
    }


    public function getOwnerCode(): string
    {
        return 'incident';
    }


    public function setRepeatedIncident(?Incident $repeatedIncident): self
    {
        $this->repeatedIncident = $repeatedIncident;

        return $this;
    }


    public function getRepeatedIncident(): ?Incident
    {
        return $this->repeatedIncident;
    }

}
