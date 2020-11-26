<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Action\ActionTaskType;
use ANOITCOM\IMSBundle\Repository\Incident\Action\ActionTaskTypeRepository;

class ActionTaskTypeService
{

    /**
     * @var ActionTaskTypeRepository
     */
    private $actionTaskTypeRepository;


    public function __construct(ActionTaskTypeRepository $actionTaskTypeRepository)
    {

        $this->actionTaskTypeRepository = $actionTaskTypeRepository;
    }


    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return []
     */
    public function findByAsOptions(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $entities = $this->actionTaskTypeRepository->findBy($criteria, $orderBy, $limit, $offset);

        return array_map(function (ActionTaskType $actionTaskType) {
            return [
                'id'          => $actionTaskType->getId(),
                'title'       => $actionTaskType->getTitle(),
                'handler'     => $actionTaskType->getHandler(),
                'action_code' => $actionTaskType->getActionCode(),
            ];
        }, $entities);
    }


    public function isValidTypeForAction(int $typeId, Action $action): ?bool
    {
        $actionCode = $action->getCode();
        $actionType = $this->actionTaskTypeRepository->find($typeId);

        if ( ! $actionType) {
            return false;
        }

        if ($actionType->getActionCode() !== $actionCode) {
            return false;
        }

        return true;
    }
}