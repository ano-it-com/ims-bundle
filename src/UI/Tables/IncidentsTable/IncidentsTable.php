<?php

namespace ANOITCOM\IMSBundle\UI\Tables\IncidentsTable;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\RequestDTO\ListParamsDTO;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusList;
use ANOITCOM\IMSBundle\Services\Incident\Provider\ArrayFilterCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\ArraySortingCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\BasicPaginationCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\IncidentDTOProvider;
use ANOITCOM\IMSBundle\UI\Tables\TableDataDTO;

class IncidentsTable
{

    /**
     * @var Security
     */
    private $security;

    /**
     * @var IncidentStatusList
     */
    private $incidentStatusList;

    /**
     * @var IncidentDTOProvider
     */
    private $incidentDTOProvider;


    public function __construct(
        IncidentDTOProvider $incidentDTOProvider,
        Security $security,
        IncidentStatusList $incidentStatusList
    ) {

        $this->security            = $security;
        $this->incidentStatusList  = $incidentStatusList;
        $this->incidentDTOProvider = $incidentDTOProvider;
    }


    public function handle(ListParamsDTO $listParamsDTO, ?User $user = null): TableDataDTO
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if ( ! $user && ! $user = $this->security->getUser()) {
            throw new \RuntimeException('Cant get user for query');
        }

        $queryParams = [
            $user,
            new ArrayFilterCriteria($listParamsDTO->filters),
            new ArraySortingCriteria([ $listParamsDTO->sortField, $listParamsDTO->sortDir ]),
            new BasicPaginationCriteria($listParamsDTO->page, $listParamsDTO->perPage)
        ];

        $result = $this->incidentDTOProvider->getPaginatedList(...$queryParams);

        $tableDataDTO          = new TableDataDTO();
        $tableDataDTO->rows    = $result->rows;
        $tableDataDTO->total   = $result->total;
        $tableDataDTO->perPage = $result->perPage;
        $tableDataDTO->page    = $result->page;
        $tableDataDTO->meta    = $this->getMetaForTable($queryParams);

        return $tableDataDTO;
    }


    private function getMetaForTable(array $queryParams): array
    {
        $qb = $this->incidentDTOProvider->getQueryBuilder(...$queryParams);

        return [
            'filterOptions' => [
                'status_code'   => $this->getStatusOptions($qb),
                'location_id'   => $this->getLocationsOptions($qb),
                'category_id'   => $this->getCategoriesOptions($qb),
                'created_by_id' => $this->getUsersOptions($qb),
            ]
        ];

    }


    private function getStatusOptions(QueryBuilder $qb): array
    {
        $cloned = clone $qb;
        $codes  = $cloned->resetQueryPart('groupBy')->select('incident_status.code')->distinct()->orderBy('incident_status.code', 'asc')->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return array_map(function ($code) {
            /** @var IncidentStatusInterface $statusClass */
            $statusClass = $this->incidentStatusList->getClassByCode($code);

            return [
                'id'    => $code,
                'title' => $statusClass::getTitle()
            ];

        }, array_column($codes, 'code'));
    }


    private function getLocationsOptions(QueryBuilder $qb): array
    {

        $cloned = clone $qb;
        $cloned->resetQueryPart('groupBy');
        $cloned->leftJoin('ims_incidents', 'ims_incident_locations', 'ims_incident_locations_options', 'ims_incident_locations_options.incident_id = ims_incidents.id');
        $cloned->leftJoin('ims_incident_locations_options', 'ims_locations', 'ims_locations_options', 'ims_incident_locations_options.location_id = ims_locations_options.id');
        $locations = $cloned->select('ims_locations_options.id, ims_locations_options.title')->distinct()->orderBy('ims_locations_options.id', 'asc')->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        if ( ! count($locations)) {
            return [];
        }

        return array_map(static function (array $location) {

            return [
                'id'    => $location['id'],
                'title' => $location['title'],
            ];

        }, $locations);

    }


    private function getCategoriesOptions(QueryBuilder $qb): array
    {
        $cloned = clone $qb;
        $cloned->resetQueryPart('groupBy');
        $cloned->leftJoin('ims_incidents', 'ims_incident_categories', 'ims_incident_categories_options', 'ims_incident_categories_options.incident_id = ims_incidents.id');
        $cloned->leftJoin('ims_incident_categories_options', 'ims_categories', 'ims_categories_options', 'ims_incident_categories_options.category_id = ims_categories_options.id');
        $categories = $cloned->select('ims_categories_options.id, ims_categories_options.title')->distinct()->orderBy('ims_categories_options.id', 'asc')->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        if ( ! count($categories)) {
            return [];
        }

        return array_map(static function (array $category) {

            return [
                'id'    => $category['id'],
                'title' => $category['title'],
            ];

        }, $categories);

    }


    private function getUsersOptions(QueryBuilder $qb): array
    {
        $cloned = clone $qb;
        $cloned->resetQueryPart('groupBy');
        $cloned->leftJoin('ims_incidents', 'users', 'users_options', 'users_options.id = ims_incidents.created_by_id');
        $users = $cloned->select('users_options.id, users_options.lastname, users_options.firstname')
                        ->distinct()->orderBy('users_options.lastname', 'asc')
                        ->execute()
                        ->fetchAll(FetchMode::ASSOCIATIVE);

        return array_map(static function (array $user) {

            return [
                'id'    => $user['id'],
                'title' => $user['lastname'] . ' ' . $user['firstname'],
            ];

        }, $users);

    }

}