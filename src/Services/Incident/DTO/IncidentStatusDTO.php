<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class IncidentStatusDTO implements IncidentPartDTOInterface
{

    public $id;

    public $createdBy;

    public $createdById;

    public $code;

    public $title;

    public $createdAt;

    public $ttl;

    public $incidentId;


    public static function fromRow(array $row): self
    {
        $dto = new self();

        $dto->id          = $row['id'];
        $dto->code        = $row['code'];
        $dto->createdAt   = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $dto->createdById = $row['created_by_id'];
        $dto->incidentId  = $row['incident_id'];

        return $dto;
    }
}