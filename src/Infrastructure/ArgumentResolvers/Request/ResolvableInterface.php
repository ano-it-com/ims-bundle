<?php

namespace ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request;

use Symfony\Component\HttpFoundation\Request;

interface ResolvableInterface
{

    public static function fromRequest(Request $request): self;
}