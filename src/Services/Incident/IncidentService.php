<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateIncidentDTO;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Types\ActionTypeList;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusNew;
use ANOITCOM\IMSBundle\Entity\Incident\Category\Category;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Entity\Location\Location;
use ANOITCOM\IMSBundle\Services\File\FileService;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentDTO;
use ANOITCOM\IMSBundle\Services\Incident\Provider\ArrayFilterCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\ArraySortingCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\BasicPaginationCriteria;
use ANOITCOM\IMSBundle\Services\Incident\Provider\IncidentDTOProvider;
use ANOITCOM\IMSBundle\Services\PermissionsService\IncidentProvider;
use ANOITCOM\IMSBundle\Services\Users\GroupsService;

class IncidentService
{

    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ActionService
     */
    private $actionService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var CategoriesService
     */
    private $categoriesService;

    /**
     * @var GroupsService
     */
    private $groupsService;

    /**
     * @var ActionTypeList
     */
    private $actionTypeList;

    /**
     * @var ActionTaskTypeService
     */
    private $actionTaskTypeService;

    /**
     * @var IncidentStatusService
     */
    private $incidentStatusService;

    /**
     * @var IncidentProvider
     */
    private $incidentProvider;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ActionTaskService
     */
    private $actionTaskService;

    /**
     * @var IncidentDTOProvider
     */
    private $incidentDTOProvider;


    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        ActionService $actionService,
        LocationService $locationService,
        CategoriesService $categoriesService,
        GroupsService $groupsService,
        ActionTypeList $actionTypeList,
        ActionTaskTypeService $actionTaskTypeService,
        IncidentStatusService $incidentStatusService,
        IncidentProvider $incidentProvider,
        FileService $fileService,
        ActionTaskService $actionTaskService,
        IncidentDTOProvider $incidentDTOProvider
    ) {

        $this->security              = $security;
        $this->em                    = $em;
        $this->actionService         = $actionService;
        $this->locationService       = $locationService;
        $this->categoriesService     = $categoriesService;
        $this->groupsService         = $groupsService;
        $this->actionTypeList        = $actionTypeList;
        $this->actionTaskTypeService = $actionTaskTypeService;
        $this->incidentStatusService = $incidentStatusService;
        $this->incidentProvider      = $incidentProvider;
        $this->fileService           = $fileService;
        $this->actionTaskService     = $actionTaskService;
        $this->incidentDTOProvider   = $incidentDTOProvider;
    }


    public function createIncident(CreateIncidentDTO $createIncidentDTO, ?User $user = null): Incident
    {
        if ( ! $user) {
            /** @var User|null $user */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $user = $this->security->getUser();
            if ( ! $user) {
                throw new \RuntimeException('Can\'t resolve user to create incident');
            }
        }

        $this->em->beginTransaction();

        try {
            $now = new \DateTimeImmutable();

            $incident = new Incident();
            $incident->setTitle($createIncidentDTO->title);
            $incident->setDescription($createIncidentDTO->description);
            $incident->setSource($createIncidentDTO->source);
            $incident->setCoverage($createIncidentDTO->coverage);
            $incident->setSpread($createIncidentDTO->spread);
            $incident->setImportance($createIncidentDTO->importance);
            $incident->setDate($now);
            $incident->setCreatedAt($now);
            $incident->setCreatedBy($user);
            $incident->setUpdatedAt($now);
            $incident->setUpdatedBy($user);
            $incident->setDeleted(false);

            foreach ($createIncidentDTO->categories as $categoryId) {
                $categoryReference = $this->em->getReference(Category::class, $categoryId);
                $incident->addCategory($categoryReference);
            }

            foreach ($createIncidentDTO->locations as $locationId) {
                $locationReference = $this->em->getReference(Location::class, $locationId);
                $incident->addLocation($locationReference);
            }

            foreach ($createIncidentDTO->responsibleGroups as $responsibleGroupId) {
                $responsibleGroupReference = $this->em->getReference(UserGroup::class, $responsibleGroupId);
                $incident->addResponsibleGroup($responsibleGroupReference);
            }

            if ($createIncidentDTO->repeatedIncidentId) {
                $repeatedIncidentReference = $this->em->getReference(Incident::class, $createIncidentDTO->repeatedIncidentId);
                $incident->setRepeatedIncident($repeatedIncidentReference);
            }

            $this->em->persist($incident);

            $status = $this->incidentStatusService->createStatus($incident, IncidentStatusNew::getCode(), $user);

            $this->em->flush();

            if ($createIncidentDTO->files) {
                $this->fileService->attachFilesTo($incident, $createIncidentDTO->files);
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->actions as $actionDTO) {
                $this->actionService->createActions($actionDTO, $incident, $user);
            }

            $this->em->commit();

        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $incident;
    }


    public function getIncidentMeta(): array
    {
        return [
            'locations'            => $this->locationService->findByAsOptions([], [ 'level' => 'asc', 'title' => 'asc' ]),
            'categories'           => $this->categoriesService->findByAsOptions([], [ 'id' => 'asc' ]),
            'groups'               => $this->groupsService->getAllCanBeResponsibleForActionAsOptions(),
            'actions'              => $this->actionTypeList->getOptions(),
            'action_task_types'    => $this->actionTaskTypeService->findByAsOptions([], [ 'title' => 'asc' ]),
            'action_task_statuses' => $this->actionTaskService->getActionTaskStatusesAsOptions(),

        ];
    }


    public function getIncidentByIdAsDTO(int $incidentId, User $user): ?IncidentDTO
    {
        return $this->incidentDTOProvider->getOneById($incidentId, $user);
    }


    public function updateIncident(int $incidentId, CreateIncidentDTO $createIncidentDTO, ?User $user = null): Incident
    {
        if ( ! $user) {
            /** @var User|null $user */
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $user = $this->security->getUser();
            if ( ! $user) {
                throw new \RuntimeException('Can\'t resolve user to update incident');
            }
        }

        $incident = $this->incidentProvider->getById($incidentId, $user);

        if ( ! $incident) {
            throw new \RuntimeException('Incident not found');
        }

        $this->em->beginTransaction();

        try {
            $now = new \DateTimeImmutable();

            $incident->setTitle($createIncidentDTO->title);
            $incident->setDescription($createIncidentDTO->description);
            $incident->setSource($createIncidentDTO->source);
            $incident->setCoverage($createIncidentDTO->coverage);
            $incident->setSpread($createIncidentDTO->spread);
            $incident->setImportance($createIncidentDTO->importance);
            $incident->setUpdatedAt($now);
            $incident->setUpdatedBy($user);

            // обновляем категории
            // TODO - потом нормально
            foreach ($incident->getCategories() as $category) {
                $incident->removeCategory($category);
            }

            foreach ($createIncidentDTO->categories as $categoryId) {
                $categoryReference = $this->em->getReference(Category::class, $categoryId);
                $incident->addCategory($categoryReference);
            }

            foreach ($incident->getLocations() as $location) {
                $incident->removeLocation($location);
            }
            foreach ($createIncidentDTO->locations as $locationId) {
                $locationReference = $this->em->getReference(Location::class, $locationId);
                $incident->addLocation($locationReference);
            }

            $currentUsedResponsibleGroups = [];
            foreach ($incident->getActions() as $action) {
                $currentUsedResponsibleGroups[] = $action->getResponsibleGroup()->getId();
            }

            foreach ($incident->getResponsibleGroups() as $responsibleGroup) {
                if ( ! in_array($responsibleGroup->getId(), $currentUsedResponsibleGroups, true)) {
                    $incident->removeResponsibleGroup($responsibleGroup);

                }
            }

            foreach ($createIncidentDTO->responsibleGroups as $responsibleGroupId) {
                $responsibleGroupReference = $this->em->getReference(UserGroup::class, $responsibleGroupId);
                $incident->addResponsibleGroup($responsibleGroupReference);
            }

            if ($createIncidentDTO->files) {
                $this->fileService->attachFilesTo($incident, $createIncidentDTO->files);
            }

            /** @var CreateActionDTO $actionDTO */
            foreach ($createIncidentDTO->actions as $actionDTO) {
                $this->actionService->createActions($actionDTO, $incident, $user);
            }

            if ($createIncidentDTO->repeatedIncidentId) {
                $repeatedIncidentReference = $this->em->getReference(Incident::class, $createIncidentDTO->repeatedIncidentId);
                $incident->setRepeatedIncident($repeatedIncidentReference);
            }

            $this->em->flush();

            $this->em->commit();

        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $incident;
    }


    public function getByTitleOptions(string $searchString, int $limit, ?User $user): array
    {
        if ( ! $searchString) {
            return [];
        }
        $queryParams = [
            $user,
            new ArrayFilterCriteria([ 'title' => $searchString ]),
            new ArraySortingCriteria([ 'ims_incidents.title', 'asc' ]),
            new BasicPaginationCriteria(1, 20)
        ];

        $qb = $this->incidentDTOProvider->getQueryBuilder(...$queryParams);

        $rows = $qb->select('ims_incidents.id, ims_incidents.title')->setMaxResults($limit)->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return array_map(function ($row) {
            return [
                'id'    => $row['id'],
                'title' => $row['title']
            ];
        }, $rows);
    }

}