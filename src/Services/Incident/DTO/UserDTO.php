<?php

namespace ANOITCOM\IMSBundle\Services\Incident\DTO;

class UserDTO implements IncidentPartDTOInterface
{

    public $id;

    public $username;

    public $email;

    public $lastName;

    public $firstName;

    public $url;


    public static function fromRow($row): self
    {
        $dto = new self();

        $dto->id        = $row['id'];
        $dto->username  = $row['username'];
        $dto->email     = $row['email'];
        $dto->lastName  = $row['lastname'];
        $dto->firstName = $row['firstname'];
        $dto->url       = '/wiki/user/' . $dto->username;

        return $dto;
    }
}