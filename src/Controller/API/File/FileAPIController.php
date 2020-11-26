<?php

namespace ANOITCOM\IMSBundle\Controller\API\File;

use ANOITCOM\Wiki\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Infrastructure\Exceptions\ValidationException;
use ANOITCOM\IMSBundle\Infrastructure\Response\ResponseFactory;
use ANOITCOM\IMSBundle\Services\File\FileService;

class FileAPIController extends AbstractController
{

    /**
     * @var Security
     */
    private $security;

    /**
     * @var FileService
     */
    private $fileService;


    public function __construct(
        FileService $fileService,
        Security $security
    ) {

        $this->security    = $security;
        $this->fileService = $fileService;
    }


    /**
     * @Route("/file/upload", name="ims_file_upload", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fileUpload(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if ( ! $user) {
            throw new AccessDeniedHttpException();
        }

        /** @var UploadedFile $file */
        $uploadedFile = $request->files->get('file');

        if ( ! $uploadedFile) {
            throw new ValidationException([ 'file' => 'File not found' ]);
        }
        if ( ! $uploadedFile->isValid()) {
            throw new ValidationException([ 'file' => 'File not valid' ]);
        }

        $file = $this->fileService->storeDraft($uploadedFile, $user);

        return ResponseFactory::success([ 'id' => $file->getId() ]);
    }


    /**
     * @Route("/file/download/{fileId}", name="ims_file_download", methods={"GET"})
     * @param Request $request
     *
     */
    public function downloadFile(int $fileId)
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if ( ! $user) {
            throw new AccessDeniedHttpException();
        }

        return $this->fileService->makeResponse($fileId);


    }

}