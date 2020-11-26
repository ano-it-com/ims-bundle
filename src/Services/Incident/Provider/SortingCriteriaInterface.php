<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Query\QueryBuilder;

interface SortingCriteriaInterface
{

    public function apply(QueryBuilder $queryBuilder): void;
}