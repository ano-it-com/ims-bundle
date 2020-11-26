<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class CreateActionTaskDTO implements ResolvableInterface
{

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     */
    public $type;

    /**
     * @Assert\Type("array")
     */
    public $inputData = [];

    /**
     * @Assert\Type("array")
     */
    public $reportData = [];

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $filesInput = [];

    public $filesReport = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();

        $dto->type        = $data['type'] ?? null;
        $dto->inputData   = $data['inputData'] ?? [];
        $dto->reportData  = $data['reportData'] ?? [];
        $dto->filesInput  = $data['filesInput'] ?? [];
        $dto->filesReport = $data['filesReport'] ?? [];

        return $dto;
    }
}