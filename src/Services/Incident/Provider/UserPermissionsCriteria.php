<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use ANOITCOM\IMSBundle\Services\PermissionsService\PermissionsProvider;

class UserPermissionsCriteria
{

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;


    public function __construct(PermissionsProvider $permissionsProvider)
    {

        $this->permissionsProvider = $permissionsProvider;
    }


    public function apply(QueryBuilder $qb, User $user)
    {
        $incidentRestrictions  = $this->permissionsProvider->getStatusRestrictions('can_view_incident_by_status', $user);
        $actionRestrictions    = $this->permissionsProvider->getStatusRestrictions('can_view_action_by_status', $user);
        $isByResponsibleOnly   = $this->permissionsProvider->userHasPermission('can_view_only_as_responsible', $user);
        $canViewWithoutActions = $this->permissionsProvider->userHasPermission('can_view_incident_without_actions', $user);

        $this->addIncidentStatusClauses($qb, $incidentRestrictions);
        $this->addActionStatusClauses($qb, $actionRestrictions, $canViewWithoutActions);
        $this->addCanViewWithoutActionsClauses($qb, $canViewWithoutActions);
        $this->addIsByResponsibleOnlyClauses($qb, $isByResponsibleOnly, $user);
    }


    private function addIncidentStatusClauses(QueryBuilder $qb, array $incidentRestrictions): void
    {
        if ( ! count($incidentRestrictions)) {
            //не находить ничего
            $qb->andWhere('ims_incidents.id = 0');

            return;
        }

        $orExpr = $qb->expr()->orX();

        foreach ($incidentRestrictions as $statusCode => $allow) {
            if ( ! $allow) {
                continue;
            }

            $orExpr->add('incident_status.code = :incident_' . $statusCode);
            $qb->setParameter('incident_' . $statusCode, $statusCode);
        }

        $qb->andWhere($orExpr);

    }


    private function addActionStatusClauses(QueryBuilder $qb, array $actionRestrictions, bool $canViewWithoutActions): void
    {
        if ( ! count($actionRestrictions)) {
            //не находить ничего
            $qb->andWhere('ims_incidents.id = 0');

            return;
        }

        $orExpr = $qb->expr()->orX();

        if ($canViewWithoutActions) {
            $orExpr->add('(SELECT COUNT(*) FROM ims_actions ims_actions_empty_count WHERE ims_actions_empty_count.incident_id = ims_incidents.id) = 0');
        }

        foreach ($actionRestrictions as $statusCode => $allow) {
            if ( ! $allow) {
                continue;
            }

            $orExpr->add('action_status.code = :action_' . $statusCode);
            $qb->setParameter('action_' . $statusCode, $statusCode);
        }

        $qb->andWhere($orExpr);

    }


    private function addCanViewWithoutActionsClauses(QueryBuilder $qb, bool $canViewWithoutActions): void
    {
        if ( ! $canViewWithoutActions) {
            // если actions нет - не выполнится
            $qb->andWhere('ims_actions.deleted <> true');

        }
    }


    private function addIsByResponsibleOnlyClauses(QueryBuilder $qb, bool $isByResponsibleOnly, User $user): void
    {
        if ( ! $isByResponsibleOnly) {
            return;
        }

        $groups = $user->getGroups();

        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->getId();
        }

        $qb
            ->andWhere('action_statuses.responsible_group_id in (:groupsIds)')
            ->setParameter('groupsIds', $groupsIds, Connection::PARAM_INT_ARRAY);

    }

}