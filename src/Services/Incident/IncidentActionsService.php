<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\ActionForActionsDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\ActionWithCommentDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\AddActionTasksForActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionsForIncidentDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\IncidentActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\SetActionTaskStatusDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\UpdateActionTaskReportDTO;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusApproving;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusClarification;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusClosed;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusCorrection;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusInWork;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusNew;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusOnConfirmation;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusRejected;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusClosed;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusInWork;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionStatus;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Entity\Incident\IncidentStatus;
use ANOITCOM\IMSBundle\Infrastructure\Exceptions\ValidationException;
use ANOITCOM\IMSBundle\Services\Comment\CommentService;
use ANOITCOM\IMSBundle\Services\File\FileService;
use ANOITCOM\IMSBundle\Services\PermissionsService\GroupsProvider;
use ANOITCOM\IMSBundle\Services\PermissionsService\IncidentProvider;

class IncidentActionsService
{

    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var IncidentProvider
     */
    private $incidentProvider;

    /**
     * @var IncidentStatusService
     */
    private $incidentStatusService;

    /**
     * @var ActionStatusService
     */
    private $actionStatusService;

    /**
     * @var GroupsProvider
     */
    private $groupsProvider;

    /**
     * @var ActionService
     */
    private $actionService;

    /**
     * @var ActionTaskService
     */
    private $actionTaskService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @var FileService
     */
    private $fileService;


    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        IncidentProvider $incidentProvider,
        IncidentStatusService $incidentStatusService,
        ActionStatusService $actionStatusService,
        GroupsProvider $groupsProvider,
        ActionService $actionService,
        ActionTaskService $actionTaskService,
        CommentService $commentService,
        FileService $fileService
    ) {

        $this->security              = $security;
        $this->em                    = $em;
        $this->incidentProvider      = $incidentProvider;
        $this->incidentStatusService = $incidentStatusService;
        $this->actionStatusService   = $actionStatusService;
        $this->groupsProvider        = $groupsProvider;
        $this->actionService         = $actionService;
        $this->actionTaskService     = $actionTaskService;
        $this->commentService        = $commentService;
        $this->fileService           = $fileService;
    }


    public function toConfirmation(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        // ответсвенный - Модератор
        $responsibleGroup = $this->groupsProvider->getModeratorGroup();

        try {
            /** @var IncidentStatus $currentStatus */
            $currentStatus = $incident->getStatus();

            if ($currentStatus->getCode() !== IncidentStatusInWork::getCode()) {
                $this->incidentStatusService->createStatus($incident, IncidentStatusInWork::getCode(), $user);
            }

            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);
                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                // процесс с модерацией?
                if ( ! $this->actionService->isWithModeration($action)) {
                    throw new ValidationException([ 'actions' => 'Нельзя отправить на подтверждение действие для группы, работающей без потверждений' ]);
                }

                $this->actionStatusService->createStatus($action, ActionStatusOnConfirmation::getCode(), $user, $responsibleGroup);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }


    }


    public function toWork(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        try {
            /** @var IncidentStatus $currentStatus */
            $currentStatus = $incident->getStatus();

            if ($currentStatus->getCode() !== IncidentStatusInWork::getCode()) {
                $this->incidentStatusService->createStatus($incident, IncidentStatusInWork::getCode(), $user);
            }

            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                // процесс с модерацией?
                if ($this->actionService->isWithModeration($action)) {
                    throw new ValidationException([ 'actions' => 'Нельзя отправить в работу действие для группы, работающей с потверждений' ]);
                }

                $responsibleGroup = $action->getResponsibleGroup();

                if ( ! $responsibleGroup) {
                    throw new \InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $this->actionStatusService->createStatus($action, ActionStatusNew::getCode(), $user, $responsibleGroup);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

    }


    public function confirm(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        try {
            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                // процесс с модерацией?
                if ( ! $this->actionService->isWithModeration($action)) {
                    throw new ValidationException([ 'actions' => 'Нельзя отправить на подтверждение действие для группы, работающей без потверждений' ]);
                }

                /** @var ActionStatus $currentActionStatus */
                $currentActionStatus = $action->getStatus();

                if ($currentActionStatus->getCode() !== ActionStatusOnConfirmation::getCode()) {
                    throw new ValidationException([ 'actions' => 'Нельзя подтвердить действие, не находящееся на подтверждении' ]);
                }

                $responsibleGroup = $action->getResponsibleGroup();

                if ( ! $responsibleGroup) {
                    throw new \InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $this->actionStatusService->createStatus($action, ActionStatusNew::getCode(), $user, $responsibleGroup);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }


    }


    public function reject(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        try {
            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                // процесс с модерацией?
                if ( ! $this->actionService->isWithModeration($action)) {
                    throw new ValidationException([ 'actions' => 'Нельзя отправить на подтверждение действие для группы, работающей без потверждений' ]);
                }

                /** @var ActionStatus $currentActionStatus */
                $currentActionStatus = $action->getStatus();

                if ($currentActionStatus->getCode() !== ActionStatusOnConfirmation::getCode()) {
                    throw new ValidationException([ 'actions' => 'Нельзя отклонить действие, не находящееся на подтверждении' ]);
                }

                $responsibleGroup = $action->getResponsibleGroup();

                if ( ! $responsibleGroup) {
                    throw new \InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $this->actionStatusService->createStatus($action, ActionStatusRejected::getCode(), $user, $responsibleGroup);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function close(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        try {
            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                /** @var ActionStatus $currentActionStatus */
                $currentActionStatus = $action->getStatus();

                if ($currentActionStatus->getCode() !== ActionStatusApproving::getCode()) {
                    throw new ValidationException([ 'actions' => 'Нельзя закрыть действие, не находящееся на одобрении' ]);
                }

                // ответсвенный - Супервизор
                $responsibleGroup = $this->groupsProvider->getSupervisorGroup();

                $this->actionStatusService->createStatus($action, ActionStatusClosed::getCode(), $user, $responsibleGroup);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function takeInWorkAsResponsibleUser(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        try {
            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                /** @var ActionStatus $currentActionStatus */
                $currentActionStatus = $action->getStatus();

                if ($currentActionStatus->getCode() !== ActionStatusNew::getCode()) {
                    throw new ValidationException([ 'actions' => 'Нельзя взять в работу действие, не в статусе "Новое"' ]);
                }

                $responsibleGroup = $action->getResponsibleGroup();

                if ( ! $responsibleGroup) {
                    throw new \InvalidArgumentException('Responsible group not found for action ' . $action->getId());
                }

                $action->setResponsibleUser($user);

                $this->actionStatusService->createStatus($action, ActionStatusInWork::getCode(), $user, $responsibleGroup, $user);
            }

            $this->em->flush();

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function toClarification(ActionWithCommentDTO $actionWithCommentDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }

        $incident = $this->incidentProvider->getById($actionWithCommentDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($actionWithCommentDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $actionWithCommentDTO->actionId . ' not found');
        }

        $isWithModeration = $this->actionService->isWithModeration($action);

        $responsibleGroupSupervisor = $this->groupsProvider->getSupervisorGroup();

        $this->em->beginTransaction();

        try {
            $this->commentService->createComment($actionWithCommentDTO, $user, $responsibleGroupSupervisor);

            // если без модерации - ну супервизора
            if ($isWithModeration) {
                $responsibleGroupModerator = $this->groupsProvider->getModeratorGroup();
                $this->actionStatusService->createStatus($action, ActionStatusClarification::getCode(), $user, $responsibleGroupModerator);
            }
            $this->actionStatusService->createStatus($action, ActionStatusClarification::getCode(), $user, $responsibleGroupSupervisor);

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function toApproving(ActionForActionsDTO $actionForActionsDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }
        $incident = $this->getIncident($actionForActionsDTO, $user);

        $actionIds = $actionForActionsDTO->actions;

        $this->em->beginTransaction();

        $responsibleGroupModerator  = $this->groupsProvider->getModeratorGroup();
        $responsibleGroupSupervisor = $this->groupsProvider->getSupervisorGroup();
        try {
            foreach ($actionIds as $actionId) {
                $action = $incident->getActionBy($actionId);

                if ( ! $action) {
                    throw new \InvalidArgumentException('Action with id ' . $actionId . ' not found');
                }

                /** @var ActionStatus $currentActionStatus */
                $currentActionStatus = $action->getStatus();

                if ( ! in_array($currentActionStatus->getCode(), [ ActionStatusInWork::getCode(), ActionStatusCorrection::getCode() ], true)) {
                    throw new ValidationException([ 'actions' => 'Нельзя передать на одобрение, если статус действия не "В работе" или "На коррекции"' ]);
                }

                $isWithModeration = $this->actionService->isWithModeration($action);

                // если без модерации - на супервизора
                if ($isWithModeration) {
                    $this->actionStatusService->createStatus($action, ActionStatusApproving::getCode(), $user, $responsibleGroupModerator);
                }
                $this->actionStatusService->createStatus($action, ActionStatusApproving::getCode(), $user, $responsibleGroupSupervisor);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function toCorrection(ActionWithCommentDTO $actionWithCommentDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }

        $incident = $this->incidentProvider->getById($actionWithCommentDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($actionWithCommentDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $actionWithCommentDTO->actionId . ' not found');
        }

        /** @var ActionStatus $currentActionStatus */
        $currentActionStatus = $action->getStatus();

        if ($currentActionStatus->getCode() !== ActionStatusApproving::getCode()) {
            throw new ValidationException([ 'actions' => 'Нельзя передать действие в коррекцию, если оно не находится на статусе "Одобрение"' ]);
        }

        $isWithModeration = $this->actionService->isWithModeration($action);

        // ищем старые статусы, чтобы понять на какой возвращать
        $statusToReturn = $this->actionService->getStatusToReturn($action);

        if ( ! $statusToReturn) {
            throw new \InvalidArgumentException('Can\'t found status to return back');
        }

        $statusCodeToSet       = $statusToReturn->getCode();
        $responsibleGroupToSet = $statusToReturn->getResponsibleGroup();
        $responsibleUserToSet  = $statusToReturn->getResponsibleUser();

        $this->em->beginTransaction();

        try {
            $this->commentService->createComment($actionWithCommentDTO, $user, $responsibleGroupToSet);

            // если без модерации - ну супервизора
            $responsibleGroupModerator = $this->groupsProvider->getModeratorGroup();
            // не создаем промежуточный статус, если уточнение запрашивал сам модератор
            if ($isWithModeration && $responsibleGroupToSet->getId() !== $responsibleGroupModerator->getId()) {
                $this->actionStatusService->createStatus($action, ActionStatusApproving::getCode(), $user, $responsibleGroupModerator);
            }
            $this->actionStatusService->createStatus($action, $statusCodeToSet, $user, $responsibleGroupToSet, $responsibleUserToSet);

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function backFromClarification(ActionWithCommentDTO $actionWithCommentDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }

        $incident = $this->incidentProvider->getById($actionWithCommentDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($actionWithCommentDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $actionWithCommentDTO->actionId . ' not found');
        }

        /** @var ActionStatus $currentActionStatus */
        $currentActionStatus = $action->getStatus();

        if ($currentActionStatus->getCode() !== ActionStatusClarification::getCode()) {
            throw new ValidationException([ 'actions' => 'Нельзя вернуть действие с уточнения, не находящееся на уточнении' ]);
        }

        $isWithModeration = $this->actionService->isWithModeration($action);

        // ищем старые статусы, чтобы понять на какой возвращать
        $statusToReturn = $this->actionService->getStatusToReturn($action);

        if ( ! $statusToReturn) {
            throw new \InvalidArgumentException('Can\'t found status to return back');
        }

        $statusCodeToSet       = $statusToReturn->getCode();
        $responsibleGroupToSet = $statusToReturn->getResponsibleGroup();
        $responsibleUserToSet  = $statusToReturn->getResponsibleUser();

        $this->em->beginTransaction();

        try {
            $this->commentService->createComment($actionWithCommentDTO, $user, $responsibleGroupToSet);

            // если без модерации - ну супервизора
            $responsibleGroupModerator = $this->groupsProvider->getModeratorGroup();
            // не создаем промежуточный статус, если уточнение запрашивал сам модератор
            if ($isWithModeration && $responsibleGroupToSet->getId() !== $responsibleGroupModerator->getId()) {
                $this->actionStatusService->createStatus($action, ActionStatusClarification::getCode(), $user, $responsibleGroupModerator);
            }
            $this->actionStatusService->createStatus($action, $statusCodeToSet, $user, $responsibleGroupToSet, $responsibleUserToSet);

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function closeIncident(IncidentActionDTO $incidentActionDTO): void
    {
        $user = $this->getUser();

        $incident = $this->incidentProvider->getById($incidentActionDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        foreach ($incident->getActions() as $action) {
            if ( ! in_array($action->getStatusCode(), [ ActionStatusRejected::getCode(), ActionStatusClosed::getCode() ], true)) {
                throw new ValidationException([ 'actions' => 'Нельзя закрыть фейк, пока не закрыты или не отменены все его действия' ]);
            }
        }

        $this->em->beginTransaction();

        try {
            $this->incidentStatusService->createStatus($incident, IncidentStatusClosed::getCode(), $user);

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    /**
     * @param CreateActionsForIncidentDTO $createActionsForIncidentDTO
     *
     * @return Action[]
     * @throws \Throwable
     */
    public function addActionsToIncident(CreateActionsForIncidentDTO $createActionsForIncidentDTO): array
    {
        $created = [];

        $user = $this->getUser();

        $incident = $this->incidentProvider->getById($createActionsForIncidentDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $this->em->beginTransaction();

        try {
            /** @var CreateActionDTO $actionDTO */
            foreach ($createActionsForIncidentDTO->actions as $actionDTO) {
                $actions = $this->actionService->createActions($actionDTO, $incident, $user);

                foreach ($actions as $action) {
                    $created[] = $action;
                }
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $created;

    }


    public function setActionTaskStatus(SetActionTaskStatusDTO $setActionTaskStatusDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }

        $incident = $this->incidentProvider->getById($setActionTaskStatusDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($setActionTaskStatusDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $setActionTaskStatusDTO->actionId . ' not found');
        }

        $actionTask = $action->getActionTaskById($setActionTaskStatusDTO->actionTaskId);

        if ( ! $actionTask) {
            throw new \InvalidArgumentException('Action Task with id ' . $setActionTaskStatusDTO->actionTaskId . ' not found');
        }

        $this->em->beginTransaction();

        try {
            $this->actionTaskService->createStatus($actionTask, $setActionTaskStatusDTO->statusCode, $user);

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function addActionTaskToAction(AddActionTasksForActionDTO $addActionTaskToActionDTO): void
    {
        $user = $this->getUser();

        $incident = $this->incidentProvider->getById($addActionTaskToActionDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($addActionTaskToActionDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $addActionTaskToActionDTO->actionId . ' not found');
        }

        $this->em->beginTransaction();

        try {
            foreach ($addActionTaskToActionDTO->actionTasks as $actionTaskDTO) {
                $this->actionTaskService->createActionTask($actionTaskDTO, $action, $user);
            }

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    public function updateActionTaskReport(UpdateActionTaskReportDTO $updateActionTaskDTO, User $user = null): void
    {
        if($user === null) {
            $user = $this->getUser();
        }

        $incident = $this->incidentProvider->getById($updateActionTaskDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        $action = $incident->getActionBy($updateActionTaskDTO->actionId);

        if ( ! $action) {
            throw new \InvalidArgumentException('Action with id ' . $updateActionTaskDTO->actionId . ' not found');
        }

        $actionTask = $action->getActionTaskById($updateActionTaskDTO->actionTaskId);

        if ( ! $actionTask) {
            throw new \InvalidArgumentException('Action Task with id ' . $updateActionTaskDTO->actionTaskId . ' not found');
        }

        $this->em->beginTransaction();

        try {
            $actionTask->setReportData($updateActionTaskDTO->reportData);

            if ($updateActionTaskDTO->filesReport) {
                $this->fileService->attachFilesTo($actionTask, $updateActionTaskDTO->filesReport, 'action_task_report');
            }

            $this->em->flush();

            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }


    private function getUser(): User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if ( ! $user) {
            throw new AccessDeniedException();
        }

        return $user;
    }


    private function getIncident(ActionForActionsDTO $actionForActionsDTO, User $user): Incident
    {
        $incident = $this->incidentProvider->getById($actionForActionsDTO->incidentId, $user);

        if ( ! $incident) {
            throw new AccessDeniedException();
        }

        return $incident;
    }

}