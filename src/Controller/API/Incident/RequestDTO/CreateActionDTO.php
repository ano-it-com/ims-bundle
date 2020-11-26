<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class CreateActionDTO implements ResolvableInterface
{

    /**
     * @Assert\NotBlank
     */
    public $title;

    /**
     * @Assert\NotBlank
     */
    public $code;

    public $description;

    /**
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $responsibleGroup = [];

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionTaskDTO",
     *          message="Неверная структура рекомендации"
     *      ),
     *     @Assert\NotBlank(),
     * })
     * @Assert\Valid
     */
    public $tasks = [];

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

        return self::fromArray($data);
    }


    public static function fromArray(array $data): ResolvableInterface
    {
        $dto = new self();

        $dto->code             = $data['code'] ?? null;
        $dto->title            = $data['title'] ?? null;
        $dto->description      = $data['description'] ?? null;
        $dto->responsibleGroup = $data['responsibleGroup'] ?? [];
        $dto->files = $data['files'] ?? [];

        $tasks = $data['tasks'] ?? [];

        foreach ($tasks as $task) {
            $dto->tasks[] = CreateActionTaskDTO::fromArray($task);
        }

        return $dto;
    }
}