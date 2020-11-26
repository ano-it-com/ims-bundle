<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class ActionTaskDTO implements IncidentPartDTOInterface
{

    public $id;

    public $actionId;

    public $statusId;

    /** @var ActionTaskStatusDTO */
    public $status;

    public $statuses = [];

    public $createdBy;

    public $createdById;

    public $typeId;

    public $typeTitle;

    public $typeHandler;

    public $createdAt;

    public $inputData = [];

    public $reportData = [];

    public $rights = [];

    public $filesInput = [];

    public $filesReport = [];


    public static function fromRow(array $row): self
    {
        $dto = new self();

        $dto->id       = $row['id'];
        $dto->actionId = $row['action_id'];
        $dto->typeId   = $row['type_id'];
        $dto->statusId = $row['status_id'];

        $dto->createdAt   = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $dto->createdById = $row['created_by_id'];
        $dto->inputData   = $row['input_data'] ? json_decode($row['input_data'], true, 512, JSON_THROW_ON_ERROR) : [];
        $dto->reportData  = $row['report_data'] ? json_decode($row['report_data'], true, 512, JSON_THROW_ON_ERROR) : [];

        return $dto;
    }
}