<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Query\QueryBuilder;

interface PaginationCriteriaInterface
{

    public function apply(QueryBuilder $queryBuilder): void;


    public function getPage(): int;


    public function getPerPage(): int;
}