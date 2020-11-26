<?php

namespace ANOITCOM\IMSBundle\Controller\API\RequestDTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use ANOITCOM\IMSBundle\Infrastructure\ArgumentResolvers\Request\ResolvableInterface;

class ListParamsDTO implements ResolvableInterface
{

    /**
     * @Assert\Type("string")
     */
    public $sortField = 'id';

    /**
     * @Assert\Type("string")
     */
    public $sortDir = 'asc';

    /**
     * @Assert\Type("int")
     */
    public $perPage = 10;

    /**
     * @Assert\Type("int")
     */
    public $page = 1;

    /**
     * @Assert\Type("array")
     */
    public $filters = [];


    public static function fromRequest(Request $request): ResolvableInterface
    {
        $dto = new self();

        $data = $request->query->all();

        $dto->sortField = $data['sort']['field'] ?? $dto->sortField;
        $dto->sortDir   = $data['sort']['dir'] ?? $dto->sortDir;
        $dto->perPage   = isset($data['perPage']) ? (int)$data['perPage'] : $dto->perPage;
        $dto->page      = isset($data['page']) ? (int)$data['page'] : $dto->page;
        $dto->filters   = $data['filter'] ?? $dto->filters;

        return $dto;
    }
}