<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionTaskDTO;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Status\ActionTaskStatusEmpty;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Status\ActionTaskStatusList;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Types\ActionTaskTypeInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Types\ActionTaskTypeList;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTask;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTaskStatus;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTaskType;
use ANOITCOM\IMSBundle\Infrastructure\Exceptions\ValidationException;
use ANOITCOM\IMSBundle\Services\File\FileService;

class ActionTaskService
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
     * @var ActionTaskTypeService
     */
    private $actionTaskTypeService;

    /**
     * @var ActionTaskStatusList
     */
    private $actionTaskStatusList;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ActionTaskTypeList
     */
    private $actionTaskTypeList;


    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        ActionTaskTypeService $actionTaskTypeService,
        ActionTaskStatusList $actionTaskStatusList,
        ActionTaskTypeList $actionTaskTypeList,
        FileService $fileService
    ) {

        $this->security              = $security;
        $this->em                    = $em;
        $this->actionTaskTypeService = $actionTaskTypeService;
        $this->actionTaskStatusList  = $actionTaskStatusList;
        $this->fileService           = $fileService;
        $this->actionTaskTypeList    = $actionTaskTypeList;
    }


    /**
     * @param CreateActionTaskDTO $actionTaskDTO
     * @param Action              $action
     * @param User|null           $user
     *
     * @return ActionTask
     */
    public function createActionTask(CreateActionTaskDTO $actionTaskDTO, Action $action, ?User $user = null): ActionTask
    {
        if ( ! $user) {
            /** @var User|null $user */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $user = $this->security->getUser();
            if ( ! $user) {
                throw new \RuntimeException('Can\'t resolve user to create action task');
            }
        }

        $now = new \DateTimeImmutable();

        $actionTask = new ActionTask();

        if ( ! $this->actionTaskTypeService->isValidTypeForAction($actionTaskDTO->type, $action)) {
            throw new ValidationException([ 'actionTask' => 'Неверный тип рекомендации для текущего действия: ' . $actionTaskDTO->type ]);
        }

        $typeReference = $this->em->getReference(ActionTaskType::class, $actionTaskDTO->type);

        $actionTask->setType($typeReference);
        $actionTask->setAction($action);
        $actionTask->setCreatedAt($now);
        $actionTask->setCreatedBy($user);
        $actionTask->setUpdatedAt($now);
        $actionTask->setUpdatedBy($user);
        $actionTask->setDeleted(false);
        $actionTask->setInputData($actionTaskDTO->inputData);
        $actionTask->setReportData($actionTaskDTO->reportData);

        $this->em->persist($actionTask);

        $this->createStatus($actionTask, ActionTaskStatusEmpty::getCode(), $user);

        $action->addActionTask($actionTask);

        $this->em->flush();

        if ($actionTaskDTO->filesInput) {
            $this->fileService->attachFilesWithCopyTo($actionTask, $actionTaskDTO->filesInput, 'action_task_input');
        }

        if ($actionTaskDTO->filesReport) {
            $this->fileService->attachFilesWithCopyTo($actionTask, $actionTaskDTO->filesReport, 'action_task_report');
        }

        return $actionTask;


    }


    public function createStatus(ActionTask $actionTask, string $statusCode, User $user): ActionTaskStatus
    {
        if ( ! $this->actionTaskStatusList->hasByCode($statusCode)) {
            throw new \InvalidArgumentException('Action Task status with code ' . $statusCode . ' not found!');
        }

        $now = new \DateTimeImmutable();

        $actionTaskStatus = new ActionTaskStatus();
        $actionTaskStatus->setCreatedAt($now);
        $actionTaskStatus->setCreatedBy($user);
        $actionTaskStatus->setActionTask($actionTask);
        $actionTaskStatus->setCode($statusCode);

        $this->em->persist($actionTaskStatus);

        $actionTask->setStatus($actionTaskStatus);

        $this->em->flush();

        return $actionTaskStatus;
    }


    public function getActionTaskStatusesAsOptions(): array
    {
        $classes = $this->actionTaskTypeList->getAllClasses();

        $options = [];

        /** @var ActionTaskTypeInterface $class */
        foreach ($classes as $class) {
            $taskTypeCode = $class::getCode();
            $statusCodes  = $class::getAllowedStatusCodes();

            foreach ($statusCodes as $statusCode) {
                if ( ! $this->actionTaskStatusList->hasByCode($statusCode)) {
                    continue;
                }

                $statusClass = $this->actionTaskStatusList->getByCode($statusCode);
                $statusCode  = $statusClass::getCode();
                $statusTitle = $statusClass::getTitle();

                if ( ! isset($options[$statusCode])) {
                    $options[$statusCode] = [
                        'id'       => $statusCode,
                        'title'    => $statusTitle,
                        'handlers' => []
                    ];
                }

                if ( ! in_array($taskTypeCode, $options[$statusCode]['handlers'], true)) {
                    $options[$statusCode]['handlers'][] = $taskTypeCode;
                }
            }
        }

        return $options;

    }
}