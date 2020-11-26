<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

use ANOITCOM\IMSBundle\Entity\Location\Location;

class LocationDTO implements IncidentPartDTOInterface
{

    public $id;

    public $title;


    public static function fromEntity(Location $location): self
    {
        $dto        = new self;
        $dto->title = $location->getTitle();
        $dto->id    = $location->getId();

        return $dto;
    }


    public static function fromRow(array $row): self
    {
        $dto = new self();

        $dto->title = $row['title'];
        $dto->id    = (int)$row['id'];

        return $dto;
    }
}