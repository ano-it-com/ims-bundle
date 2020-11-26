<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Query\QueryBuilder;

interface FilterCriteriaInterface
{

    public function apply(QueryBuilder $queryBuilder): void;
}