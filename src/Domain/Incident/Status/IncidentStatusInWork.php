<?php

namespace ANOITCOM\IMSBundle\Domain\Incident\Status;

class IncidentStatusInWork implements IncidentStatusInterface
{

    public const CODE = 'in_work';


    public static function getCode(): string
    {
        return self::CODE;
    }


    public static function getTitle(): string
    {
        return 'В работе';
    }


    public static function getTtl(): int
    {
        return 60 * 60;
    }
}