<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class ActionForActionsDTO implements ResolvableInterface
{

    /**
     * @Assert\Type("int")
     * @Assert\NotBlank(),
     */
    public $incidentId;

    /**
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $actions = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $dto->incidentId = $data['incidentId'] ?? null;
        $dto->actions    = $data['actions'] ?? [];

        return $dto;
    }
}