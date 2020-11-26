<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Comment;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Domain\File\FileOwnerInterface;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Repository\Incident\Comment\CommentRepository;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\Table(name="ims_comments")
 */
class Comment implements FileOwnerInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity=Incident::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incident;

    /**
     * @ORM\ManyToOne(targetEntity=Action::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=true)
     */
    private $action;

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
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $targetGroup;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $level;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;


    public function __construct()
    {
        $this->deleted   = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getText(): ?string
    {
        return $this->text;
    }


    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }


    public function getLevel(): ?string
    {
        return $this->level;
    }


    public function setLevel(string $level): self
    {
        $this->level = $level;

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


    public function getAction(): ?Action
    {
        return $this->action;
    }


    public function setAction(?Action $action): self
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


    public function getTargetGroup(): UserGroup
    {
        return $this->targetGroup;
    }


    public function setTargetGroup(UserGroup $targetGroup): self
    {
        $this->targetGroup = $targetGroup;

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


    public function getOwnerCode(): string
    {
        return 'comment';
    }
}