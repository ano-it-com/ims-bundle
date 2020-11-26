<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class ActionStatusDTO implements IncidentPartDTOInterface
{

    public $id;

    public $createdBy;

    public $createdById;

    public $responsibleGroupId;

    public $responsibleGroup;

    public $responsibleUser;

    public $code;

    public $title;

    public $createdAt;

    public $ttl;

    public $actionId;


    public static function fromRow(array $row): self
    {
        $dto = new self();

        $dto->id                 = $row['id'];
        $dto->code               = $row['code'];
        $dto->createdAt          = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $dto->responsibleGroupId = $row['responsible_group_id'];
        $dto->createdById        = $row['created_by_id'];
        $dto->actionId           = $row['action_id'];

        return $dto;
    }
}