<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class ActionWithCommentDTO implements ResolvableInterface
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
     * @Assert\Type("string")
     * @Assert\NotBlank(),
     */
    public $comment;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $files = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $dto->incidentId = $data['incidentId'] ?? null;
        $dto->actionId   = $data['actionId'] ?? null;
        $dto->comment    = $data['comment'] ?? null;
        $dto->files      = $data['files'] ?? [];

        return $dto;
    }
}