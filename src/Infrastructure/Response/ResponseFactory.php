<?php

namespace ANOITCOM\IMSBundle\Infrastructure\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseFactory
{

    public const STATUS_SUCCESS = 200;
    public const STATUS_VALIDATION_ERROR = 400;
    public const STATUS_SERVER_ERROR = 500;


    public static function success(array $data): JsonResponse
    {
        return new JsonResponse($data, self::STATUS_SUCCESS);
    }


    public static function validationError(array $data): JsonResponse
    {
        return new JsonResponse($data, self::STATUS_VALIDATION_ERROR);
    }


    public static function serverError(array $data): JsonResponse
    {
        return new JsonResponse($data, self::STATUS_SERVER_ERROR);
    }
}