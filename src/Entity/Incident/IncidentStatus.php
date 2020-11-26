<?php

namespace ANOITCOM\IMSBundle\Entity\Incident;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Incident\IncidentStatusRepository;

/**
 * @ORM\Entity(repositoryClass=IncidentStatusRepository::class)
 * @ORM\Table(name="ims_incident_statuses")
 */
class IncidentStatus
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
     * @ORM\ManyToOne(targetEntity=Incident::class, inversedBy="statuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incident;


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


    public function getIncident(): Incident
    {
        return $this->incident;
    }


    public function setIncident($incident): self
    {
        $this->incident = $incident;

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
