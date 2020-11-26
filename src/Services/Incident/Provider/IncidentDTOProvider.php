<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionTaskDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentDTO;
use ANOITCOM\IMSBundle\Services\PermissionsService\ActionRightsResolver;
use ANOITCOM\IMSBundle\Services\PermissionsService\GroupsProvider;
use ANOITCOM\IMSBundle\Services\PermissionsService\PermissionsProvider;

class IncidentDTOProvider
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPermissionsCriteria
     */
    private $userPermissionsCriteria;

    /**
     * @var IncidentDTOBuilder
     */
    private $incidentDTOBuilder;

    /**
     * @var ActionRightsResolver
     */
    private $actionRightsResolver;

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;

    /**
     * @var GroupsProvider
     */
    private $groupsProvider;


    public function __construct(
        EntityManagerInterface $em,
        UserPermissionsCriteria $userPermissionsCriteria,
        IncidentDTOBuilder $incidentDTOBuilder,
        ActionRightsResolver $actionRightsResolver,
        PermissionsProvider $permissionsProvider,
        GroupsProvider $groupsProvider
    ) {
        $this->em                      = $em;
        $this->userPermissionsCriteria = $userPermissionsCriteria;
        $this->incidentDTOBuilder      = $incidentDTOBuilder;
        $this->actionRightsResolver    = $actionRightsResolver;
        $this->permissionsProvider     = $permissionsProvider;
        $this->groupsProvider          = $groupsProvider;
    }


    /**
     * @param User                             $user
     * @param FilterCriteriaInterface|null     $filterCriteria
     * @param ArraySortingCriteria|null        $sortingCriteria
     * @param PaginationCriteriaInterface|null $paginationCriteria
     *
     * @return PaginatedListResult
     */
    public function getPaginatedList(
        User $user,
        FilterCriteriaInterface $filterCriteria,
        ArraySortingCriteria $sortingCriteria,
        PaginationCriteriaInterface $paginationCriteria
    ): PaginatedListResult {
        $qb = $this->getBaseQuery();
        $this->applyUserPermissions($qb, $user);

        $filterCriteria->apply($qb);

        $sortingCriteria->apply($qb);

        $total = $this->countTotalRows($qb);

        $paginationCriteria->apply($qb);

        $stmt = $qb->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $DTOs = $this->buildDTOsWithUserRights($rows, $user);

        return new PaginatedListResult($DTOs, $total, $paginationCriteria->getPage(), $paginationCriteria->getPerPage());


    }


    /**
     * @param User                             $user
     * @param FilterCriteriaInterface|null     $filterCriteria
     * @param ArraySortingCriteria|null        $sortingCriteria
     * @param PaginationCriteriaInterface|null $paginationCriteria
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(
        User $user,
        FilterCriteriaInterface $filterCriteria,
        ArraySortingCriteria $sortingCriteria,
        PaginationCriteriaInterface $paginationCriteria
    ): QueryBuilder {
        $qb = $this->getBaseQuery();
        $this->applyUserPermissions($qb, $user);

        $filterCriteria->apply($qb);

        $sortingCriteria->apply($qb);

        return $qb;
    }


    private function getBaseQuery(): QueryBuilder
    {
        $queryBuilder = $this->em
            ->getConnection()
            ->createQueryBuilder()
            ->from('ims_incidents', 'ims_incidents')
            ->select('ims_incidents.*')
            // join status table
            ->leftJoin('ims_incidents', 'ims_incident_statuses', 'incident_status', 'ims_incidents.status_id = incident_status.id')
            // join status table
            ->leftJoin('ims_incidents', 'ims_actions', 'ims_actions', 'ims_incidents.id = ims_actions.incident_id')
            ->leftJoin('ims_actions', 'ims_action_statuses', 'action_status', 'ims_actions.status_id = action_status.id')
            // join action statuses responsible groups
            ->leftJoin('ims_actions', 'ims_action_statuses', 'action_statuses', 'ims_actions.id = action_statuses.action_id')
            ->andWhere('ims_incidents.deleted <> true')
            ->groupBy('ims_incidents.id');

        return $queryBuilder;
    }


    private function applyUserPermissions(QueryBuilder $qb, User $user): void
    {
        $this->userPermissionsCriteria->apply($qb, $user);
    }


    private function buildDTOsWithUserRights(array $rows, User $user): array
    {
        $dtos = $this->incidentDTOBuilder->build($rows);

        if ( ! count($dtos)) {
            return [];
        }

        // TODO - make right
        // убираем лишнее
        $dtos = $this->removeWrongActions($dtos, $user);

        $this->loadAndAddRightsToDTOs($dtos, $user);

        return $dtos;
    }


    private function countTotalRows(QueryBuilder $qb): int
    {
        $qbSubQuery = clone $qb;

        $qbCountStmt = $this
            ->em
            ->getConnection()
            ->createQueryBuilder()
            ->from('(' . $qbSubQuery->getSQL() . ') as t')
            ->select('count(*)')
            ->setParameters($qbSubQuery->getParameters(), $qbSubQuery->getParameterTypes())
            ->execute();

        return (int)$qbCountStmt->fetchColumn(0);
    }


    private function loadAndAddRightsToDTOs(array $incidentDTOs, User $user): void
    {
        // add permissions
        $rights = $this->actionRightsResolver->getActionRightsForIncidents($incidentDTOs, $user);

        foreach ($incidentDTOs as $incidentDTO) {
            $incidentRights = $rights[$incidentDTO->id] ?? null;
            if ( ! $incidentRights) {
                return;
            }

            $onlyIncidentRights = $incidentRights;
            unset($onlyIncidentRights['actions']);

            $incidentDTO->rights = $onlyIncidentRights;

            /** @var ActionDTO $actionDTO */
            foreach ($incidentDTO->actions as $actionDTO) {
                $actionRights = $incidentRights['actions'][$actionDTO->id] ?? null;
                if ( ! $actionRights) {
                    continue;
                }

                $onlyActionRights = $actionRights;
                unset($onlyActionRights['actionTasks']);

                $actionDTO->rights = $onlyActionRights;

                /** @var ActionTaskDTO $actionTaskDTO */
                foreach ($actionDTO->tasks as $actionTaskDTO) {
                    $actionTaskRights = $incidentRights['actions']['actionTasks'][$actionTaskDTO->id] ?? null;
                    if ( ! $actionTaskRights) {
                        continue;
                    }

                    $actionTaskDTO->rights = $actionTaskRights;
                }
            }
        }

    }


    public function canAccess(int $incidentId, User $user): bool
    {
        $qb = $this->getBaseQuery();
        $this->applyUserPermissions($qb, $user);

        return (bool)$qb->andWhere('ims_incidents.id = :id')->setParameter('id', $incidentId)->execute()->rowCount();
    }


    public function getOneById(int $incidentId, User $user): ?IncidentDTO
    {
        $qb = $this->getBaseQuery();
        $this->applyUserPermissions($qb, $user);

        $qb->andWhere('ims_incidents.id = :id')->setParameter('id', $incidentId);

        $stmt = $qb->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        if ( ! count($rows)) {
            return null;
        }

        $DTOs = $this->buildDTOsWithUserRights($rows, $user);

        return reset($DTOs);
    }


    private function removeWrongActions(array $dtos, User $user): array
    {
        $incidentRestrictions  = $this->permissionsProvider->getStatusRestrictions('can_view_incident_by_status', $user);
        $actionRestrictions    = $this->permissionsProvider->getStatusRestrictions('can_view_action_by_status', $user);
        $isByResponsibleOnly   = $this->permissionsProvider->userHasPermission('can_view_only_as_responsible', $user);
        $canViewWithoutActions = $this->permissionsProvider->userHasPermission('can_view_incident_without_actions', $user);

        $nonModeratedGroups = $this->groupsProvider->getAllCanProcessWithoutModeration();

        $nonModeratedGroups = array_map(static function (UserGroup $group) {
            return $group->getId();
        }, $nonModeratedGroups);

        $groups = $user->getGroups();

        $isModerator = false;

        $groupsIds = [];
        foreach ($groups as $group) {
            if ($group->getName() === 'moderator') {
                $isModerator = true;
            }
            $groupsIds[] = $group->getId();
        }

        /** @var IncidentDTO $dto */
        foreach ($dtos as $dtoKey => $dto) {
            $actions = $dto->actions;
            foreach ($actions as $actionKey => $action) {
                // по ответственным действия
                if ($isByResponsibleOnly && ! $isModerator) {
                    if ( ! in_array($action->responsibleGroupId, $groupsIds, true)) {
                        unset($actions[$actionKey]);
                    }
                }
                if ($isModerator) {
                    if (in_array($action->responsibleGroupId, $nonModeratedGroups, true)) {
                        unset($actions[$actionKey]);
                    }
                }
                $actionStatusCode = $action->status->code;

                $can = $actionRestrictions[$actionStatusCode] ?? false;
                if ( ! $can) {
                    unset($actions[$actionKey]);
                }

            }
            $dto->actions = array_values($actions);
        }

        return $dtos;
    }

}