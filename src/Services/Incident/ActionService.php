<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionTaskDTO;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusNeedConfirmation;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Types\ActionTypeList;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionStatus;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Event\ActionCreatedEvent;
use ANOITCOM\IMSBundle\Infrastructure\Exceptions\ValidationException;
use ANOITCOM\IMSBundle\Services\File\FileService;
use ANOITCOM\IMSBundle\Services\PermissionsService\GroupsProvider;
use ANOITCOM\IMSBundle\Services\PermissionsService\PermissionsProvider;

class ActionService
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
     * @var ActionTaskService
     */
    private $actionTaskService;

    /**
     * @var ActionTypeList
     */
    private $actionTypeList;

    /**
     * @var ActionStatusService
     */
    private $actionStatusService;

    /**
     * @var GroupsProvider
     */
    private $groupsProvider;

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        ActionTaskService $actionTaskService,
        ActionTypeList $actionTypeList,
        ActionStatusService $actionStatusService,
        GroupsProvider $groupsProvider,
        PermissionsProvider $permissionsProvider,
        FileService $fileService,
        EventDispatcherInterface $eventDispatcher
    ) {

        $this->security            = $security;
        $this->em                  = $em;
        $this->actionTaskService   = $actionTaskService;
        $this->actionTypeList      = $actionTypeList;
        $this->actionStatusService = $actionStatusService;
        $this->groupsProvider      = $groupsProvider;
        $this->permissionsProvider = $permissionsProvider;
        $this->fileService = $fileService;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param CreateActionDTO $actionDTO
     * @param Incident        $incident
     * @param User|null       $user
     *
     * @return Action[]
     */
    public function createActions(CreateActionDTO $actionDTO, Incident $incident, ?User $user = null): array
    {
        if ( ! $user) {
            /** @var User|null $user */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $user = $this->security->getUser();
            if ( ! $user) {
                throw new \RuntimeException('Can\'t resolve user to create action');
            }
        }

        $now = new \DateTimeImmutable();

        $actions = [];

        /** @var int $groupId */
        foreach ($actionDTO->responsibleGroup as $groupId) {
            $action = new Action();
            $action->setTitle($actionDTO->title);
            $action->setDescription($actionDTO->description);

            $code = $actionDTO->code;

            if ( ! $this->actionTypeList->hasByCode($code)) {
                throw new ValidationException([ 'action' => 'Указан неверный тип действия: ' . $code ]);
            }

            $action->setCode($actionDTO->code);
            $action->setCreatedAt($now);
            $action->setCreatedBy($user);
            $action->setUpdatedAt($now);
            $action->setUpdatedBy($user);
            $action->setResponsibleUser(null);
            $action->setDeleted(false);
            $action->setIncident($incident);

            $groupReference = $this->em->getReference(
                UserGroup::class,
                $groupId
            );

            $action->setResponsibleGroup($groupReference);

            $this->em->persist($action);

            $responsibleGroupPermissionCode = 'is_supervisor';

            $responsibleGroup = $this->groupsProvider->getOneForPermission($responsibleGroupPermissionCode);

            if ( ! $responsibleGroup) {
                throw new \InvalidArgumentException('Responsible group not found for code ' . $responsibleGroupPermissionCode);
            }

            $this->actionStatusService->createStatus($action, ActionStatusNeedConfirmation::getCode(), $user, $responsibleGroup);

            $incident->addAction($action);

            $this->em->flush();

            $event = new ActionCreatedEvent($action);
            $this->eventDispatcher->dispatch($event);

            if ($actionDTO->files) {
                $this->fileService->attachFilesWithCopyTo($action, $actionDTO->files);
            }


            /** @var CreateActionTaskDTO $taskDTO */
            foreach ($actionDTO->tasks as $taskDTO) {
                $this->actionTaskService->createActionTask($taskDTO, $action, $user);
            }

        }

        $this->em->flush();

        return $actions;


    }


    public function isWithModeration(Action $action): bool
    {
        $group = $action->getResponsibleGroup();

        return ! $this->permissionsProvider->groupHasPermission('work_without_moderation', $group);

    }


    public function getStatusToReturn(Action $action): ?ActionStatus
    {
        $status = $action->getStatus();
        if ( ! $status) {
            throw new \InvalidArgumentException('No current status for action');
        }

        $currentStatusCode = $status->getCode();

        $statuses = $action->getStatuses()->toArray();

        usort($statuses, static function (ActionStatus $a, ActionStatus $b) {
            $aId = $a->getId();
            $bId = $b->getId();
            if ($aId === $bId) {
                return 0;
            }

            return ($aId > $bId) ? -1 : 1;
        });

        /** @var ActionStatus $status */
        foreach ($statuses as $status) {
            if ($status->getCode() !== $currentStatusCode) {
                return $status;
            }
        }

        return null;

    }
}