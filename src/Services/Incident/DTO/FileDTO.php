<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class FileDTO implements IncidentPartDTOInterface
{

    public $id;

    public $ownerCode;

    public $ownerId;

    public $path;

    public $originalName;

    public $size;

    public $ext;


    public static function fromRow(array $row): self
    {
        $dto = new self;

        $dto->id           = $row['id'];
        $dto->ownerCode    = $row['owner_code'];
        $dto->ownerId      = $row['owner_id'];
        $dto->originalName = $row['original_name'];
        $dto->size         = $row['size'];
        $dto->ext          = strtolower(pathinfo($row['path'], PATHINFO_EXTENSION));

        return $dto;
    }
}