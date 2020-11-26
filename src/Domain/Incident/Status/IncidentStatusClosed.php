<?php

namespace ANOITCOM\IMSBundle\Domain\Incident\Status;

class IncidentStatusClosed implements IncidentStatusInterface
{

    public const CODE = 'closed';


    public static function getCode(): string
    {
        return self::CODE;
    }


    public static function getTitle(): string
    {
        return 'Закрыт';
    }


    public static function getTtl(): int
    {
        return 0;
    }
}