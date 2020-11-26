<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class CategoryDTO implements IncidentPartDTOInterface
{

    public $id;

    public $title;


    public static function fromRow(array $row): self
    {
        $dto        = new self;
        $dto->title = $row['title'];
        $dto->id    = $row['id'];

        return $dto;
    }
}