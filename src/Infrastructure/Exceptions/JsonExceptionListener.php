<?php

namespace ANOITCOM\IMSBundle\Infrastructure\Exceptions;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use ANOITCOM\IMSBundle\Infrastructure\Response\ResponseFactory;

class JsonExceptionListener
{

    /**
     * @var KernelInterface
     */
    private $kernel;


    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request   = $event->getRequest();

        if (stripos($request->getContentType(), 'json') === false) {
            return;
        }

        if ($exception instanceof ValidationException) {
            $errors = $exception->getErrors();

            $response = ResponseFactory::validationError($errors);

        } else {
            if ($this->kernel->isDebug()) {
                return;
            }
            $response = ResponseFactory::serverError([]);
        }

        $event->setResponse($response);

    }

}