<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusList;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionStatus;
use ANOITCOM\IMSBundle\Event\ActionStatusCreatedEvent;

class ActionStatusService
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ActionStatusList
     */
    private $actionStatusList;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(
        EntityManagerInterface $em,
        ActionStatusList $actionStatusList,
        EventDispatcherInterface $eventDispatcher
    )
    {

        $this->em               = $em;
        $this->actionStatusList = $actionStatusList;
        $this->eventDispatcher = $eventDispatcher;
    }


    public function createStatus(Action $action, string $statusCode, User $user, UserGroup $responsibleGroup, ?User $responsibleUser = null): ActionStatus
    {
        if ( ! $this->actionStatusList->hasByCode($statusCode)) {
            throw new \InvalidArgumentException('Action status with code ' . $statusCode . ' not found!');
        }

        $now = new \DateTimeImmutable();

        $status = new ActionStatus();
        $status->setCode($statusCode);
        $status->setCreatedAt($now);
        $status->setCreatedBy($user);
        $status->setAction($action);
        $status->setResponsibleGroup($responsibleGroup);
        $status->setResponsibleUser($responsibleUser);

        $this->em->persist($status);

        $action->setStatus($status);

        $this->em->flush();

        $event = new ActionStatusCreatedEvent($status);
        $this->eventDispatcher->dispatch($event);

        return $status;
    }
}