<?php

namespace ANOITCOM\IMSBundle\Services\Comment;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\ActionWithCommentDTO;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Entity\Incident\Comment\Comment;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Services\File\FileService;

class CommentService
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FileService
     */
    private $fileService;


    public function __construct(EntityManagerInterface $em, FileService $fileService)
    {

        $this->em          = $em;
        $this->fileService = $fileService;
    }


    public function createComment(ActionWithCommentDTO $actionWithCommentDTO, User $user, UserGroup $targetGroup): Comment
    {
        $incidentId = $actionWithCommentDTO->incidentId;
        $actionId   = $actionWithCommentDTO->actionId;
        $text       = $actionWithCommentDTO->comment;
        $fileIds    = $actionWithCommentDTO->files;

        $incidentRef = $this->em->getReference(Incident::class, $incidentId);
        $actionRef   = $this->em->getReference(Action::class, $actionId);

        $now = new \DateTimeImmutable();

        $comment = new Comment();
        $comment->setText($text);
        $comment->setLevel('action');
        $comment->setIncident($incidentRef);
        $comment->setAction($actionRef);
        $comment->setCreatedAt($now);
        $comment->setCreatedBy($user);
        $comment->setUpdatedAt($now);
        $comment->setUpdatedBy($user);
        $comment->setTargetGroup($targetGroup);
        $comment->setDeleted(false);

        $this->em->persist($comment);
        $this->em->flush();

        if (count($fileIds)) {
            $this->fileService->attachFilesTo($comment, $fileIds);
        }

        return $comment;


    }
}