<?php

namespace ANOITCOM\IMSBundle\Services\File;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ANOITCOM\IMSBundle\Domain\File\FileOwnerInterface;
use ANOITCOM\IMSBundle\Entity\File\File;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Repository\File\FileRepository;
use ANOITCOM\IMSBundle\Services\Incident\DTO\FileDTO;

class FileService
{

    /**
     * @var string
     */
    private $filesPath;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;


    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, FileRepository $fileRepository, UrlGeneratorInterface $generator)
    {
        $this->filesPath      = $kernel->getProjectDir() . '/var/storage/uploads/ims/files';
        $this->em             = $em;
        $this->fileRepository = $fileRepository;
        $this->generator      = $generator;
    }


    public function storeDraft(UploadedFile $uploadedFile, User $user): File
    {
        $originalFilename = $uploadedFile->getClientOriginalName();
        $size             = $uploadedFile->getSize();

        $safeFilename = uniqid('ims_', true);

        $newFilename = $safeFilename . '.' . $uploadedFile->getClientOriginalExtension();

        $uploadedFile->move(
            $this->filesPath,
            $newFilename
        );

        $file = new File();
        $file->setOwnerCode('draft');
        $file->setOwnerId(0);
        $file->setPath($newFilename);
        $file->setOriginalName($originalFilename);
        $file->setSize($size);
        $file->setDeleted(true);
        $file->setCreatedAt(new \DateTimeImmutable());
        $file->setCreatedBy($user);

        $this->em->persist($file);

        $this->em->flush();

        return $file;

    }


    public function getFileContent(int $fileId): string
    {
        $file = $this->fileRepository->find($fileId);

        if ( ! $file) {
            throw new \RuntimeException('File not found');
        }

        return file_get_contents($this->filesPath . '/' . $file->getPath());

    }


    public function getFilePath(int $fileId): string
    {
        $file = $this->fileRepository->find($fileId);

        if ( ! $file) {
            throw new \RuntimeException('File not found');
        }

        return $this->filesPath . '/' . $file->getPath();
    }


    public function makeResponse(int $fileId): BinaryFileResponse
    {
        $file = $this->fileRepository->find($fileId);

        if ( ! $file) {
            throw new \RuntimeException('File not found');
        }

        $filePath = $this->filesPath . '/' . $file->getPath();

        $response = new BinaryFileResponse($filePath);

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        $ext = strtolower($ext);

        $originalFileName = str_replace('"', '', $file->getOriginalName());

        if (in_array($ext, [
            'jpg',
            'jpeg',
            'gif',
            'tiff',
            'bmp',
            'png',
        ], true)) {
            $dispositionType = ResponseHeaderBag::DISPOSITION_INLINE;
            $response->headers->set('Content-Type', 'image/' . $ext);
        } elseif ($ext === 'pdf') {
            $dispositionType = ResponseHeaderBag::DISPOSITION_INLINE;
            $response->headers->set('Content-Type', 'application/pdf');
        } else {
            $dispositionType = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        }

        $response->headers->set('Content-Disposition', $dispositionType . '; filename="' . $originalFileName . '"');

        return $response;


    }


    public function attachFilesTo(FileOwnerInterface $owner, array $fileIds, ?string $ownerCode = null): void
    {
        $files = $this->fileRepository->findBy([ 'id' => $fileIds, 'deleted' => true ]);

        if ( ! $ownerCode) {
            $ownerCode = $owner->getOwnerCode();
        }

        $ownerId = $owner->getId();

        foreach ($files as $file) {
            $file->setOwnerCode($ownerCode);
            $file->setOwnerId($ownerId);
            $file->setDeleted(false);
        }

        $this->em->flush();
    }


    public function attachFilesWithCopyTo(FileOwnerInterface $owner, array $fileIds, ?string $ownerCode = null): void
    {
        $files = $this->fileRepository->findBy([ 'id' => $fileIds, 'deleted' => true ]);

        if ( ! $ownerCode) {
            $ownerCode = $owner->getOwnerCode();
        }

        $ownerId   = $owner->getId();

        foreach ($files as $file) {
            $newFile = new File();

            $newFile->setOwnerCode($ownerCode);
            $newFile->setOwnerId($ownerId);
            $newFile->setPath($file->getPath());
            $newFile->setOriginalName($file->getOriginalName());
            $newFile->setSize($file->getSize());
            $newFile->setDeleted(false);
            $newFile->setCreatedAt($file->getCreatedAt());
            $newFile->setCreatedBy($file->getCreatedBy());

            $this->em->persist($newFile);
        }

        $this->em->flush();
    }

}