<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class IncidentReferenceDTO implements IncidentPartDTOInterface
{

    public $id;

    public $date;

    public $title;

    public $description;


    public static function fromRow(array $row): self
    {
        $dto              = new self();
        $dto->id          = (int)$row['id'];
        $dto->date        = (new \DateTimeImmutable($row['date']))->format('d.m.Y H:i:s');
        $dto->title       = $row['title'];
        $dto->description = $row['description'];

        return $dto;
    }
}