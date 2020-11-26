<?php

namespace ANOITCOM\IMSBundle\Entity\Incident\Action;

use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionTaskTypeRepository;

/**
 * @ORM\Entity(repositoryClass=ActionTaskTypeRepository::class)
 * @ORM\Table(name="ims_action_task_types")
 */
class ActionTaskType
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
     * @ORM\Column(type="string")
     */
    private $handler;

    /**
     * @ORM\Column(type="string")
     */
    private $actionCode;


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


    public function getHandler(): string
    {
        return $this->handler;
    }


    public function setHandler(string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }


    public function getActionCode(): string
    {
        return $this->actionCode;
    }


    public function setActionCode(string $actionCode): self
    {
        $this->actionCode = $actionCode;

        return $this;
    }

}
