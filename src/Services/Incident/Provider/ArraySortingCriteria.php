<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Query\QueryBuilder;

class ArraySortingCriteria implements SortingCriteriaInterface
{

    private $sorting = [
        'id'            => 'ims_incidents.id',
        'status_code'   => 'incident_status.code',
        'created_by_id' => 'incident.created_by_id',
        'date'          => 'ims_incidents.date',
        'title'         => 'ims_incidents.title',
        'coverage'      => 'ims_incidents.coverage',
        'spread'        => 'ims_incidents.spread',
        'importance'    => 'ims_incidents.importance',
    ];

    /**
     * @var array
     */
    private $sortingParams;


    public function __construct(array $sortingParams)
    {

        $this->sortingParams = $sortingParams;
    }


    public function apply(QueryBuilder $queryBuilder): void
    {
        if (count($this->sortingParams) !== 2) {
            return;
        }
        $field  = $this->sortingParams[0];
        $column = $this->sorting[$field] ?? null;

        if ( ! $column) {
            return;
        }

        $dir = $this->sortingParams[1];
        if ( ! in_array(strtolower($dir), [ 'asc', 'desc' ], true)) {
            $dir = 'asc';
        }

        $queryBuilder->orderBy($column, $dir);

    }
}