<?php

namespace ANOITCOM\IMSBundle\Domain\Incident\Status;

interface IncidentStatusInterface
{

    public static function getCode(): string;


    public static function getTitle(): string;


    public static function getTtl(): int;
}