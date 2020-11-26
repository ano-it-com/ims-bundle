<?php

namespace ANOITCOM\IMSBundle\UI\Tables;

class TableDataDTO
{

    public $rows = [];

    public $total;

    public $perPage;

    public $page;

    public $meta = [];


    public function toArray(): array
    {
        return (array)$this;
    }
}