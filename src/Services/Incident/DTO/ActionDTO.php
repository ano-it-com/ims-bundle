<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class ActionDTO
{

    public $id;

    public $incidentId;

    public $responsibleGroup;

    public $responsibleGroupId;

    public $responsibleUser;

    public $responsibleUserId;

    public $createdById;

    public $createdBy;

    public $createdAt;

    public $updatedAt;

    public $updatedById;

    public $updatedBy;

    public $title;

    public $description;

    public $code;

    public $actionTitle;

    public $tasks = [];

    public $statuses = [];

    public $comments = [];

    /** @var ActionStatusDTO */
    public $status;

    public $statusId;

    public $rights = [];

    public $files = [];


    public static function fromRow(array $row): self
    {
        $actionDto = new self();

        $actionDto->id                 = $row['id'];
        $actionDto->incidentId         = $row['incident_id'];
        $actionDto->createdAt          = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $actionDto->updatedAt          = (new \DateTimeImmutable($row['updated_at']))->format('d.m.Y H:i:s');
        $actionDto->createdById        = $row['created_by_id'];
        $actionDto->updatedById        = $row['updated_by_id'];
        $actionDto->title              = $row['title'];
        $actionDto->description        = $row['description'];
        $actionDto->code               = $row['code'];
        $actionDto->responsibleUserId  = $row['responsible_user_id'];
        $actionDto->responsibleGroupId = $row['responsible_group_id'];
        $actionDto->statusId           = $row['status_id'];

        return $actionDto;
    }

}