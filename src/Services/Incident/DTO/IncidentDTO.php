<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class IncidentDTO implements IncidentPartDTOInterface
{

    public $id;

    public $statusId;

    /** @var IncidentStatusDTO */
    public $status;

    public $statuses = [];

    public $createdBy;

    public $createdById;

    public $createdAt;

    public $date;

    public $title;

    public $description;

    public $sources = [];

    public $coverage;

    public $spread;

    public $importance;

    /** @var ActionDTO[] */
    public $actions = [];

    public $locations = [];

    public $categories = [];

    public $responsibleGroups = [];

    public $repeatedIncidentId;

    /** @var IncidentReferenceDTO[] */
    public $childIncidents = [];

    public $repeatedIncident;

    public $rights = [];

    public $files = [];


    public static function fromRow(array $row): self
    {
        $dto                     = new self();
        $dto->id                 = (int)$row['id'];
        $dto->createdAt          = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $dto->createdById        = $row['created_by_id'];
        $dto->date               = (new \DateTimeImmutable($row['date']))->format('d.m.Y H:i:s');
        $dto->title              = $row['title'];
        $dto->description        = $row['description'];
        $dto->sources            = $row['source'] ? json_decode($row['source'], true, 512, JSON_THROW_ON_ERROR) : [];
        $dto->coverage           = (float)$row['coverage'];
        $dto->spread             = (int)$row['spread'];
        $dto->importance         = (int)$row['importance'];
        $dto->statusId           = (int)$row['status_id'];
        $dto->repeatedIncidentId = $row['repeated_incident_id'];

        return $dto;
    }
}