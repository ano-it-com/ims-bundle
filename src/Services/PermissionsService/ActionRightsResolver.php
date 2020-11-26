<?php /** @noinspection PhpUnusedParameterInspection */

namespace ANOITCOM\IMSBundle\Services\PermissionsService;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Status\ActionTaskStatusEmpty;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusApproving;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusClarification;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusClosed;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusCorrection;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusInWork;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusNeedConfirmation;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusNew;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusOnConfirmation;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusRejected;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusClosed;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionTaskDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentDTO;

class ActionRightsResolver
{

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;

    /**
     * @var GroupsProvider
     */
    private $groupsProvider;


    public function __construct(PermissionsProvider $permissionsProvider, GroupsProvider $groupsProvider)
    {

        $this->permissionsProvider = $permissionsProvider;
        $this->groupsProvider      = $groupsProvider;
    }


    public function getActionRightsForIncidents(array $incidents, User $user): array
    {
        $permissions        = $this->permissionsProvider->getAllNonRestrictedPermissions($user);
        $canEditIncident    = $this->permissionsProvider->getStatusRestrictions('can_edit_incident_by_status', $user);
        $canEditActions     = $this->permissionsProvider->getStatusRestrictions('can_edit_action_by_status', $user);
        $nonModeratedGroups = $this->groupsProvider->getAllCanProcessWithoutModeration();

        $nonModeratedGroups = array_map(static function (UserGroup $group) {
            return $group->getId();
        }, $nonModeratedGroups);

        $rights = [];

        $arguments = [ $permissions, $canEditIncident, $canEditActions, $nonModeratedGroups, $user ];

        /** @var IncidentDTO $incidentDTO */
        foreach ($incidents as $incidentDTO) {
            $incidentId = $incidentDTO->id;

            $incidentRights = [
                'canCloseIncident' => $this->canCloseIncident($incidentDTO, ...$arguments),
                'canEditIncident'  => $this->canEditIncident($incidentDTO, ...$arguments),
                'canAddAction'     => $this->canAddAction($incidentDTO, ...$arguments),
            ];

            $actionsRights     = [];
            $actionTasksRights = [];

            foreach ($incidentDTO->actions as $actionDTO) {
                $actionsRights[$actionDTO->id] = [
                    'canEditAction'                          => $this->canEditAction($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToConfirmation'          => $this->canChangeActionToConfirmation($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToWork'                  => $this->canChangeActionToWork($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToConfirm'               => $this->canChangeActionToConfirm($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToReject'                => $this->canChangeActionToReject($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToClose'                 => $this->canChangeActionToClose($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToTakeInWork'            => $this->canChangeActionToTakeInWork($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToClarification'         => $this->canChangeActionToClarification($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToApproving'             => $this->canChangeActionToApproving($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToCorrection'            => $this->canChangeActionToCorrection($incidentDTO, $actionDTO, ...$arguments),
                    'canChangeActionToBackFromClarification' => $this->canChangeActionToBackFromClarification($incidentDTO, $actionDTO, ...$arguments),
                    'canAddActionTask'                       => $this->canAddActionTask($incidentDTO, $actionDTO, ...$arguments),
                ];

                foreach ($actionDTO->tasks as $actionTaskDTO) {
                    $actionTasksRights[$actionTaskDTO->id] = [
                        'canSetActionTaskStatus'  => $this->canSetActionTaskStatus($incidentDTO, $actionDTO, $actionTaskDTO, ...$arguments),
                        'canEditActionTask'       => $this->canEditActionTask($incidentDTO, $actionDTO, $actionTaskDTO, ...$arguments),
                        'canEditActionTaskReport' => $this->canEditActionTaskReport($incidentDTO, $actionDTO, $actionTaskDTO, ...$arguments),
                    ];
                }

            }

            $actionsRights['actionTasks'] = $actionTasksRights;

            $incidentRights['actions'] = $actionsRights;

            $rights[$incidentId] = $incidentRights;
        }

        return $rights;
    }


    private function canCloseIncident(IncidentDTO $incidentDTO, array $permissions, array $canEditIncident, array $canEditActions, array $nonModeratedGroups, User $user): bool
    {
        if ( ! $this->canEditIncident($incidentDTO, $permissions, $canEditIncident, $canEditActions, $nonModeratedGroups, $user)) {
            return false;
        }

        if ($incidentDTO->status->code === IncidentStatusClosed::getCode()) {
            return false;
        }

        foreach ($incidentDTO->actions as $actionDTO) {
            if ( ! in_array($actionDTO->status->code, [ ActionStatusClosed::getCode(), ActionStatusRejected::getCode() ], true)) {
                return false;
            }
        }

        return true;
    }


    private function canEditIncident(IncidentDTO $incidentDTO, array $permissions, array $canEditIncident, array $canEditActions, array $nonModeratedGroups, User $user): bool
    {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $statusCode = $incidentDTO->status->code;
        if ( ! $statusCode) {
            return false;
        }

        $canEdit = $canEditIncident[$statusCode] ?? false;

        return (bool)$canEdit;
    }


    private function canAddAction(IncidentDTO $incidentDTO, array $permissions, array $canEditIncident, array $canEditActions, array $nonModeratedGroups, User $user): bool
    {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        if ( ! $this->canEditIncident($incidentDTO, $permissions, $canEditIncident, $canEditActions, $nonModeratedGroups, $user)) {
            return false;
        }

        if ($incidentDTO->status->code === IncidentStatusClosed::getCode()) {
            return false;
        }

        return true;
    }


    private function canEditAction(IncidentDTO $incidentDTO, ActionDTO $actionDTO, array $permissions, array $canEditIncident, array $canEditActions, array $nonModeratedGroups, User $user): bool
    {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        if ( ! $this->canEditIncident($incidentDTO, $permissions, $canEditIncident, $canEditActions, $nonModeratedGroups, $user)) {
            return false;
        }

        $statusCode = $actionDTO->status->code;
        if ( ! $statusCode) {
            return false;
        }

        $canEdit = $canEditActions[$statusCode] ?? false;

        return (bool)$canEdit;

    }


    private function canChangeActionToConfirmation(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        // если без модерации - то нельзя
        if ( ! $this->isWithModeration($actionDTO, $nonModeratedGroups, $user)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode !== ActionStatusNeedConfirmation::getCode());

    }


    private function canChangeActionToWork(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if ($currentActionStatusCode !== ActionStatusNeedConfirmation::getCode()) {
            return false;
        }

        // если с модерацией - то нельзя
        if ($this->isWithModeration($actionDTO, $nonModeratedGroups, $user)) {
            return false;
        }

        return true;

    }


    private function canChangeActionToConfirm(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isModerator($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if ($currentActionStatusCode !== ActionStatusOnConfirmation::getCode()) {
            return false;
        }

        // если без модерации - то нельзя
        if ( ! $this->isWithModeration($actionDTO, $nonModeratedGroups, $user)) {
            return false;
        }

        return true;
    }


    private function canChangeActionToReject(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        return $this->canChangeActionToConfirm($incidentDTO, $actionDTO, $permissions, $canEditIncident, $canEditActions, $nonModeratedGroups, $user);

    }


    private function canChangeActionToClose(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode !== ActionStatusApproving::getCode());

    }


    private function canChangeActionToTakeInWork(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isExecutor($permissions)) {
            return false;
        }

        if ($actionDTO->responsibleUserId) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode !== ActionStatusNew::getCode());
    }


    private function canChangeActionToClarification(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        $currentActionStatusCode = $actionDTO->status->code;

        if ($this->isExecutor($permissions)) {
            if ($currentActionStatusCode !== ActionStatusInWork::getCode()) {
                return false;
            }

            $responsibleUserId = $actionDTO->responsibleUserId;

            if ( ! $responsibleUserId) {
                return false;
            }

            if ($user->getId() !== $responsibleUserId) {
                return false;
            }

            return true;
        }

        if ($this->isModerator($permissions)) {
            return ! ($currentActionStatusCode !== ActionStatusApproving::getCode());
        }

        return false;
    }


    private function canChangeActionToApproving(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isExecutor($permissions)) {
            return false;
        }

        $responsibleUserId = $actionDTO->responsibleUserId;

        if ( ! $responsibleUserId) {
            return false;
        }

        if ($user->getId() !== $responsibleUserId) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if ( ! in_array($currentActionStatusCode, [ ActionStatusInWork::getCode(), ActionStatusCorrection::getCode() ], true)) {
            return false;
        }

        /** @var ActionTaskDTO $actionTaskDTO */
        foreach ($actionDTO->tasks as $actionTaskDTO) {
            $actionTaskStatus = $actionTaskDTO->status;
            if ( ! $actionTaskStatus) {
                return false;
            }

            if ($actionTaskStatus->code === ActionTaskStatusEmpty::getCode()) {
                return false;
            }
        }

        return true;
    }


    private function canChangeActionToCorrection(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode !== ActionStatusApproving::getCode());

    }


    private function canChangeActionToBackFromClarification(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode !== ActionStatusClarification::getCode());
    }


    private function canAddActionTask(IncidentDTO $incidentDTO, ActionDTO $actionDTO, array $permissions, array $canEditIncident, array $canEditActions, array $nonModeratedGroups, User $user): bool
    {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        return ! ($currentActionStatusCode === ActionStatusClosed::getCode() || $currentActionStatusCode === ActionStatusOnConfirmation::getCode());
    }


    private function canSetActionTaskStatus(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        ActionTaskDTO $actionTaskDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isExecutor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if ( ! in_array($currentActionStatusCode, [ ActionStatusInWork::getCode(), ActionStatusCorrection::getCode() ], true)) {
            return false;
        }

        return true;
    }


    private function canEditActionTask(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        ActionTaskDTO $actionTaskDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isSupervisor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if (in_array($currentActionStatusCode, [ ActionStatusRejected::getCode(), ActionStatusClosed::getCode(), ActionStatusOnConfirmation::getCode() ], true)) {
            return false;
        }

        return true;
    }


    private function canEditActionTaskReport(
        IncidentDTO $incidentDTO,
        ActionDTO $actionDTO,
        ActionTaskDTO $actionTaskDTO,
        array $permissions,
        array $canEditIncident,
        array $canEditActions,
        array $nonModeratedGroups,
        User $user
    ): bool {
        if ( ! $this->isExecutor($permissions)) {
            return false;
        }

        $currentActionStatusCode = $actionDTO->status->code;

        if ( ! in_array($currentActionStatusCode, [ ActionStatusInWork::getCode(), ActionStatusCorrection::getCode() ], true)) {
            return false;
        }

        return true;
    }


    private function isWithModeration(ActionDTO $actionDTO, array $nonModeratedGroups, User $user): bool
    {
        $responsibleGroupId = $actionDTO->responsibleGroupId;

        if (in_array($responsibleGroupId, $nonModeratedGroups, true)) {
            return false;
        }

        return true;

    }


    private function isModerator(array $permissions): bool
    {
        if (in_array('is_moderator', $permissions, true)) {
            return true;
        }

        return false;
    }


    private function isSupervisor(array $permissions): bool
    {
        if (in_array('is_supervisor', $permissions, true)) {
            return true;
        }

        return false;
    }


    private function isExecutor(array $permissions): bool
    {
        if (in_array('is_executor', $permissions, true)) {
            return true;
        }

        return false;
    }

}