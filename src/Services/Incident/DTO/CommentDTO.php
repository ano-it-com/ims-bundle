<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class CommentDTO implements IncidentPartDTOInterface
{

    public $id;

    public $text;

    public $createdAt;

    public $targetGroupId;

    public $targetGroup;

    public $createdBy;

    public $createdById;

    public $files = [];


    public static function fromRow(array $row): self
    {
        $dto = new self;

        $dto->id            = $row['id'];
        $dto->text          = $row['text'];
        $dto->createdAt     = (new \DateTimeImmutable($row['created_at']))->format('d.m.Y H:i:s');
        $dto->createdById   = $row['created_by_id'];
        $dto->targetGroupId = $row['target_group_id'];

        return $dto;
    }
}