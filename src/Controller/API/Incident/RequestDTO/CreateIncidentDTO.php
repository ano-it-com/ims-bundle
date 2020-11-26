<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class CreateIncidentDTO implements ResolvableInterface
{

    /**
     * @Assert\NotBlank
     */
    public $title;

    public $description;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("string"),
     *     @Assert\NotBlank(),
     * })
     */
    public $source = [];

    /**
     * @Assert\Type("numeric")
     */
    public $coverage;

    /**
     * @Assert\Type("int")
     */
    public $spread;

    /**
     * @Assert\Type("int")
     */
    public $importance;

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $categories = [];

    /**
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $locations = [];

    /**
     * @Assert\Type("array")
     * @Assert\NotBlank(),
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $responsibleGroups = [];

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type(
     *          type="ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionDTO",
     *          message="Неверная структура действия"
     *
     *      ),
     *     @Assert\NotBlank(),
     * })
     * @Assert\Valid
     */
    public $actions = [];

    /**
     * @Assert\Type("array")
     * @Assert\All({
     *     @Assert\Type("int"),
     *     @Assert\NotBlank(),
     * })
     */
    public $files = [];

    /**
     * @Assert\Type("int")
     */
    public $repeatedIncidentId;


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = new self();

        $dto->title              = $data['title'] ?? null;
        $dto->description        = $data['description'] ?? null;
        $dto->source             = $data['source'] ?? [];
        $dto->categories         = $data['categories'] ?? [];
        $dto->coverage           = $data['coverage'] ?? null;
        $dto->spread             = $data['spread'] ?? null;
        $dto->importance         = $data['importance'] ?? null;
        $dto->locations          = $data['locations'] ?? [];
        $dto->responsibleGroups  = $data['responsibleGroups'] ?? [];
        $dto->files              = $data['files'] ?? [];
        $dto->repeatedIncidentId = $data['repeatedIncidentId'] ?? null;

        $actions = $data['actions'] ?? [];

        foreach ($actions as $action) {
            $dto->actions[] = CreateActionDTO::fromArray($action);
        }

        return $dto;
    }
}