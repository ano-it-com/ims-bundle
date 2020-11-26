<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class AddActionTasksForActionDTO implements ResolvableInterface
{

    /**
     * @Assert\Type("int")
     * @Assert\NotBlank(),
     */
    public $incidentId;

    /**
     * @Assert\Type("int")
     * @Assert\NotBlank(),
     */
    public $actionId;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionTaskDTO",
     *          message="Неверная структура действия"
     *
     *      ),
     *     @Assert\NotBlank(),
     * })
     * @Assert\Valid
     */
    public $actionTasks = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $dto->incidentId = $data['incidentId'] ?? null;
        $dto->actionId   = $data['actionId'] ?? null;

        $actionTasks = $data['actionTasks'] ?? [];

        foreach ($actionTasks as $actionTask) {
            $dto->actionTasks[] = CreateActionTaskDTO::fromArray($actionTask);
        }

        return $dto;
    }
}