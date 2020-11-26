<?php

namespace ANOITCOM\IMSBundle\Domain\File;

interface FileOwnerInterface
{

    public function getId();


    public function getOwnerCode(): string;

}