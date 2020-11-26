<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Query\QueryBuilder;

class BasicPaginationCriteria implements PaginationCriteriaInterface
{

    private $page;

    private $perPage;


    public function __construct(int $page = 1, int $perPage = 10)
    {

        $this->page    = $page;
        $this->perPage = $perPage;
    }


    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->setFirstResult(($this->page - 1) * $this->perPage);
        $queryBuilder->setMaxResults($this->perPage);
    }


    public function getPage(): int
    {
        return $this->page;
    }


    public function getPerPage(): int
    {
        return $this->perPage;
    }
}