<?php

namespace ANOITCOM\IMSBundle\Services\Incident\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Status\ActionTaskStatusInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Status\ActionTaskStatusList;
use ANOITCOM\IMSBundle\Domain\Incident\Action\ActionTask\Types\ActionTaskTypeList;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Status\ActionStatusList;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Types\ActionTypeInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Action\Types\ActionTypeList;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusList;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionStatusDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionTaskDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\ActionTaskStatusDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\CategoryDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\CommentDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\FileDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\GroupDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentPartDTOInterface;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentReferenceDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\IncidentStatusDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\LocationDTO;
use ANOITCOM\IMSBundle\Services\Incident\DTO\UserDTO;

class IncidentDTOBuilder
{

    private $connection;

    private $incidentStatusList;

    private $actionStatusList;

    private $actionTaskStatusList;

    private $actionTaskTypeList;

    /**
     * @var ActionTypeList
     */
    private $actionTypeList;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;


    public function __construct(
        Connection $connection,
        IncidentStatusList $incidentStatusList,
        ActionStatusList $actionStatusList,
        ActionTaskStatusList $actionTaskStatusList,
        ActionTaskTypeList $actionTaskTypeList,
        ActionTypeList $actionTypeList,
        UrlGeneratorInterface $generator
    ) {
        $this->connection           = $connection;
        $this->incidentStatusList   = $incidentStatusList;
        $this->actionStatusList     = $actionStatusList;
        $this->actionTaskStatusList = $actionTaskStatusList;
        $this->actionTaskTypeList   = $actionTaskTypeList;
        $this->actionTypeList       = $actionTypeList;
        $this->generator            = $generator;
    }


    public function build(array $rows): array
    {
        $incidentDTOs = array_map(static function (array $row) {
            return IncidentDTO::fromRow($row);
        }, $rows);

        $incidentRefIds = [];

        foreach ($rows as $row) {
            if ($row['repeated_incident_id'] && ! in_array($row['repeated_incident_id'], $incidentRefIds, true)) {
                $incidentRefIds[] = $row['repeated_incident_id'];
            }
        }

        $incidentIds = array_map(function ($row) {
            return $row['id'];
        }, $rows);


        $childIncidentsRefsDTOGroupedByParent = $this->loadChildRefsIncidents($incidentIds);
        $incidentRefsDTOsById         = $this->loadIncidentRefs($incidentRefIds);
        $locationsDTOsByIncidentId    = $this->loadLocations($incidentIds);
        $categoriesDTOsByIncidentId   = $this->loadCategories($incidentIds);
        $groupsDTOsByIncidentId       = $this->loadIncidentResponsibleGroups($incidentIds);
        $incidentStatusesByIncidentId = $this->loadIncidentStatuses($incidentIds);
        $actionsByIncidentId          = $this->loadActions($incidentIds);

        $actionIds = [];

        foreach ($actionsByIncidentId as $incidentId => $dtos) {
            /** @var ActionDTO $dto */
            foreach ($dtos as $dto) {
                $actionIds[] = $dto->id;
            }
        }

        $actionStatusesByActionId = $this->loadActionStatuses($actionIds);
        $actionCommentsByActionId = $this->loadActionComments($actionIds);
        $actionTasksByActionId    = $this->loadActionTasks($actionIds);

        $actionTaskIds      = [];
        $actionTaskTypesIds = [];

        foreach ($actionTasksByActionId as $incidentId => $dtos) {
            /** @var ActionTaskDTO $dto */
            foreach ($dtos as $dto) {
                $actionTaskIds[]      = $dto->id;
                $actionTaskTypesIds[] = $dto->typeId;
            }
        }

        $actionTaskStatusesByActionTaskId = $this->loadActionTaskStatuses($actionTaskIds);
        $actionTaskTypesById              = $this->loadActionTaskTypes($actionTaskTypesIds);

        [ $groupIds, $userIds ] = $this->collectUserAndGroupIds(
            $incidentDTOs,
            $incidentStatusesByIncidentId,
            $actionsByIncidentId,
            $actionStatusesByActionId,
            $actionCommentsByActionId,
            $actionTasksByActionId,
            $actionTaskStatusesByActionTaskId
        );

        $usersById = $this->loadUsers($userIds);
        $groupById = $this->loadGroups($groupIds);

        // статусам ставим группы и пользоватлей
        $this->setUsersAndGroupsForStatuses(
            $incidentStatusesByIncidentId,
            $actionStatusesByActionId,
            $actionTaskStatusesByActionTaskId,
            $usersById,
            $groupById
        );

        foreach ($incidentDTOs as $incidentDTO) {
            $incidentStatuses = $incidentStatusesByIncidentId[$incidentDTO->id] ?? [];
            if ( ! isset($incidentStatuses[$incidentDTO->statusId])) {
                throw new \RuntimeException('Incident status with id ' . $incidentDTO->statusId . ' not found');
            }
            $incidentDTO->status   = $incidentStatuses[$incidentDTO->statusId];
            $incidentDTO->statuses = array_values($incidentStatuses);

            if ( ! isset($usersById[$incidentDTO->createdById])) {
                throw new \RuntimeException('User with id ' . $incidentDTO->createdById . ' not found');
            }

            $incidentDTO->createdBy         = $usersById[$incidentDTO->createdById];
            $incidentDTO->locations         = isset($locationsDTOsByIncidentId[$incidentDTO->id]) ? array_values($locationsDTOsByIncidentId[$incidentDTO->id]) : [];
            $incidentDTO->categories        = isset($categoriesDTOsByIncidentId[$incidentDTO->id]) ? array_values($categoriesDTOsByIncidentId[$incidentDTO->id]) : [];
            $incidentDTO->responsibleGroups = isset($groupsDTOsByIncidentId[$incidentDTO->id]) ? array_values($groupsDTOsByIncidentId[$incidentDTO->id]) : [];

            if ($incidentDTO->repeatedIncidentId) {
                if ( ! isset($incidentRefsDTOsById[$incidentDTO->repeatedIncidentId])) {
                    throw new \RuntimeException('Repeated incident with id ' . $incidentDTO->repeatedIncidentId . ' not found');
                }
                $incidentDTO->repeatedIncident = $incidentRefsDTOsById[$incidentDTO->repeatedIncidentId];
            }



            if (isset($childIncidentsRefsDTOGroupedByParent[$incidentDTO->id])) {
                $incidentDTO->childIncidents = $childIncidentsRefsDTOGroupedByParent[$incidentDTO->id];
            }

            // Actions
            $incidentActions = $actionsByIncidentId[$incidentDTO->id] ?? [];
            /** @var ActionDTO $actionDTO */
            foreach ($incidentActions as $actionDTO) {
                if ( ! isset($groupById[$actionDTO->responsibleGroupId])) {
                    throw new \RuntimeException('Group with id ' . $incidentDTO->responsibleGroupId . ' not found');

                }
                $actionDTO->responsibleGroup = $groupById[$actionDTO->responsibleGroupId];

                if ($actionDTO->responsibleUserId) {
                    if ( ! isset($usersById[$actionDTO->responsibleUserId])) {
                        throw new \RuntimeException('User with id ' . $incidentDTO->responsibleUserId . ' not found');

                    }
                    $actionDTO->responsibleUser = $usersById[$actionDTO->responsibleUserId];
                }

                if ( ! isset($usersById[$actionDTO->createdById])) {
                    throw new \RuntimeException('User with id ' . $incidentDTO->createdById . ' not found');

                }
                $actionDTO->createdBy = $usersById[$actionDTO->createdById];

                if ( ! isset($usersById[$actionDTO->updatedById])) {
                    throw new \RuntimeException('User with id ' . $incidentDTO->updatedById . ' not found');

                }
                $actionDTO->updatedBy = $usersById[$actionDTO->updatedById];

                $actionStatuses = $actionStatusesByActionId[$actionDTO->id] ?? [];
                if ( ! isset($actionStatuses[$actionDTO->statusId])) {
                    throw new \RuntimeException('Action status with id ' . $actionDTO->statusId . ' not found');
                }
                $actionDTO->status   = $actionStatuses[$actionDTO->statusId];
                $actionDTO->statuses = array_values($actionStatuses);

                $actionComments      = $actionCommentsByActionId[$actionDTO->id] ?? [];
                $actionDTO->comments = array_values($actionComments);

                // ACTION TASKS
                $actionTasksForAction = $actionTasksByActionId[$actionDTO->id] ?? [];

                /** @var ActionTaskDTO $actionTaskDTO */
                foreach ($actionTasksForAction as $actionTaskDTO) {
                    $actionTaskStatuses = $actionTaskStatusesByActionTaskId[$actionTaskDTO->id] ?? [];
                    if ( ! isset($actionTaskStatuses[$actionTaskDTO->statusId])) {
                        throw new \RuntimeException('Action Task status with id ' . $actionTaskDTO->statusId . ' not found');
                    }
                    $actionTaskDTO->status   = $actionTaskStatuses[$actionTaskDTO->statusId];
                    $actionTaskDTO->statuses = array_values($actionTaskStatuses);

                    if ( ! isset($usersById[$actionTaskDTO->createdById])) {
                        throw new \RuntimeException('User with id ' . $actionTaskDTO->createdById . ' not found');

                    }
                    $actionTaskDTO->createdBy = $usersById[$actionTaskDTO->createdById];

                    if ( ! isset($actionTaskTypesById[$actionTaskDTO->typeId])) {
                        throw new \RuntimeException('Action Task Type with id ' . $actionTaskDTO->typeId . ' not found');
                    }
                    $type = $actionTaskTypesById[$actionTaskDTO->typeId];

                    $actionTaskDTO->typeTitle   = $type['title'];
                    $actionTaskDTO->typeHandler = $type['handler'];
                }

                $actionDTO->tasks = array_values($actionTasksForAction);
            }

            $incidentDTO->actions = array_values($incidentActions);
        }

        $this->loadAndAttachFiles($incidentDTOs);

        return $incidentDTOs;
    }


    private function loadLocations(array $incidentIds): array
    {
        return $this->loadMany2ManyRelationDTOGroupedByOwnerId(
            'ims_incident_locations',
            'incident_id',
            'location_id',
            'ims_locations',
            'id',
            $incidentIds,
            LocationDTO::class);

    }


    private function loadMany2ManyRelationDTOGroupedByOwnerId(
        string $pivotTable,
        string $pivotOwnerIdColumn,
        string $pivotFkColumn,
        string $relationTable,
        string $relationIdColumn,
        array $ids,
        string $dtoClass
    ): array {
        /** @var IncidentPartDTOInterface $dtoClass */

        $stmt = $this
            ->connection
            ->createQueryBuilder()
            ->from($relationTable)
            ->select($relationTable . '.*, ' . $pivotTable . '.' . $pivotOwnerIdColumn . ' as owner_id')
            ->leftJoin($relationTable, $pivotTable, $pivotTable, $relationTable . '.' . $relationIdColumn . ' = ' . $pivotTable . '.' . $pivotFkColumn)
            ->orderBy($relationTable . '.' . $relationIdColumn, 'asc')
            ->where($pivotTable . '.' . $pivotOwnerIdColumn . ' in (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $dtos = [];

        $grouped = [];
        foreach ($rows as $row) {
            if ( ! isset($dtos[$row[$relationIdColumn]])) {
                $dtos[$row[$relationIdColumn]] = $dtoClass::fromRow($row);
            }
            $grouped[$row['owner_id']][$row[$relationIdColumn]] = $dtos[$row[$relationIdColumn]];
        }

        return $grouped;

    }


    private function loadCategories(array $incidentIds): array
    {
        return $this->loadMany2ManyRelationDTOGroupedByOwnerId(
            'ims_incident_categories',
            'incident_id',
            'category_id',
            'ims_categories',
            'id',
            $incidentIds,
            CategoryDTO::class);

    }


    private function loadIncidentResponsibleGroups(array $incidentIds): array
    {
        return $this->loadMany2ManyRelationDTOGroupedByOwnerId(
            'ims_incident_groups',
            'incident_id',
            'group_id',
            'groups',
            'id',
            $incidentIds,
            GroupDTO::class);
    }


    private function loadIncidentStatuses(array $incidentIds): array
    {
        $dtoGroups = $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_incident_statuses', 'incident_id', $incidentIds, IncidentStatusDTO::class);

        foreach ($dtoGroups as $incidentId => $dtos) {
            /** @var IncidentStatusDTO $dto */
            foreach ($dtos as $dto) {
                /** @var IncidentStatusInterface $class */
                $class      = $this->incidentStatusList->getClassByCode($dto->code);
                $dto->title = $class::getTitle();
                $dto->ttl   = $class::getTtl();
            }
        }

        return $dtoGroups;

    }


    private function loadOneToManyRelationDTOGroupedByOwnerId(string $table, string $fkColumn, array $ids, string $dtoClass): array
    {
        /** @var IncidentPartDTOInterface $dtoClass */

        $stmt = $this
            ->connection
            ->createQueryBuilder()
            ->from($table)
            ->select($table . '.*')
            ->orderBy($table . '.id', 'asc')
            ->where($table . '.' . $fkColumn . ' in (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row[$fkColumn]][$row['id']] = $dtoClass::fromRow($row);
        }

        return $grouped;

    }


    private function loadActions(array $incidentIds): array
    {
        $dtoGroups = $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_actions', 'incident_id', $incidentIds, ActionDTO::class);

        foreach ($dtoGroups as $incidentId => $dtos) {
            /** @var ActionDTO $dto */
            foreach ($dtos as $dto) {
                /** @var ActionTypeInterface $actionTypeClass */
                $actionTypeClass  = $this->actionTypeList->getClassByCode($dto->code);
                $dto->actionTitle = $actionTypeClass::getTitle();
            }
        }

        return $dtoGroups;
    }


    private function loadActionStatuses(array $actionIds): array
    {
        $dtoGroups = $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_action_statuses', 'action_id', $actionIds, ActionStatusDTO::class);

        foreach ($dtoGroups as $actionId => $dtos) {
            /** @var ActionStatusDTO $dto */
            foreach ($dtos as $dto) {
                /** @var ActionStatusInterface $class */
                $class      = $this->actionStatusList->getClassByCode($dto->code);
                $dto->title = $class::getTitle();
                $dto->ttl   = $class::getTtl();
            }
        }

        return $dtoGroups;
    }


    private function loadActionComments(array $actionIds): array
    {
        return $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_comments', 'action_id', $actionIds, CommentDTO::class);
    }


    private function loadActionTasks(array $actionIds): array
    {
        return $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_action_tasks', 'action_id', $actionIds, ActionTaskDTO::class);
    }


    private function loadActionTaskStatuses(array $actionTaskIds): array
    {
        $dtoGroups = $this->loadOneToManyRelationDTOGroupedByOwnerId('ims_action_task_statuses', 'action_task_id', $actionTaskIds, ActionTaskStatusDTO::class);

        foreach ($dtoGroups as $actionId => $dtos) {
            /** @var ActionTaskStatusDTO $dto */
            foreach ($dtos as $dto) {
                /** @var ActionTaskStatusInterface $class */
                $class      = $this->actionTaskStatusList->getClassByCode($dto->code);
                $dto->title = $class::getTitle();
            }
        }

        return $dtoGroups;
    }


    private function loadActionTaskTypes(array $actionTaskTypesIds): array
    {
        $stmt = $this
            ->connection
            ->createQueryBuilder()
            ->from('ims_action_task_types')
            ->select('ims_action_task_types.*')
            ->orderBy('id', 'asc')
            ->where('ims_action_task_types.id in (:ids)')
            ->setParameter('ids', $actionTaskTypesIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['id']] = $row;
        }

        return $grouped;
    }


    private function collectUserAndGroupIds(
        array $incidentDTOs,
        array $incidentStatusesByIncidentId,
        array $actionsByIncidentId,
        array $actionStatusesByActionId,
        array $actionCommentsByActionId,
        array $actionTasksByActionId,
        array $actionTaskStatusesByActionTaskId
    ): array {
        $userIds  = [];
        $groupIds = [];

        foreach ($incidentDTOs as $incidentDTO) {
            $userIds[] = $incidentDTO->createdById;
        }

        $toCheckGrouped = [
            $incidentStatusesByIncidentId,
            $actionsByIncidentId,
            $actionStatusesByActionId,
            $actionCommentsByActionId,
            $actionTasksByActionId,
            $actionTaskStatusesByActionTaskId,
        ];

        foreach ($toCheckGrouped as $grouped) {
            foreach ($grouped as $ownerId => $group) {
                foreach ($group as $dto) {
                    if (property_exists($dto, 'createdById') && $dto->createdById) {
                        $userIds[] = $dto->createdById;
                    }
                    if (property_exists($dto, 'updateById') && $dto->updateById) {
                        $userIds[] = $dto->updateById;
                    }
                    if (property_exists($dto, 'responsibleGroupId') && $dto->responsibleGroupId) {
                        $groupIds[] = $dto->responsibleGroupId;
                    }
                    if (property_exists($dto, 'targetGroupId') && $dto->targetGroupId) {
                        $groupIds[] = $dto->targetGroupId;
                    }
                }
            }
        }

        return [ array_unique($groupIds), array_unique($userIds) ];
    }


    private function loadUsers(array $userIds): array
    {
        $stmt = $this
            ->connection
            ->createQueryBuilder()
            ->from('users')
            ->select('users.*')
            ->orderBy('id', 'asc')
            ->where('users.id in (:ids)')
            ->setParameter('ids', $userIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['id']] = UserDTO::fromRow($row);
        }

        return $grouped;
    }


    private function loadGroups(array $groupIds): array
    {
        $stmt = $this
            ->connection
            ->createQueryBuilder()
            ->from('groups')
            ->select('groups.*')
            ->orderBy('id', 'asc')
            ->where('groups.id in (:ids)')
            ->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY)
            ->execute();

        $rows = $stmt->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['id']] = GroupDTO::fromRow($row);
        }

        return $grouped;
    }


    private function setUsersAndGroupsForStatuses(
        array $incidentStatusesByIncidentId,
        array $actionStatusesByActionId,
        array $actionTaskStatusesByActionTaskId,
        array $usersById,
        array $groupById
    ): void {
        foreach ([ $incidentStatusesByIncidentId, $actionStatusesByActionId, $actionTaskStatusesByActionTaskId, ] as $grouped) {
            foreach ($grouped as $ownerId => $group) {
                foreach ($group as $dto) {
                    if (property_exists($dto, 'createdById') && $dto->createdById) {
                        if ( ! isset($usersById[$dto->createdById])) {
                            throw new \RuntimeException('User with id ' . $dto->createdById . ' not found');
                        }
                        $dto->createdBy = $usersById[$dto->createdById];
                    }

                    if (property_exists($dto, 'responsibleGroupId') && $dto->responsibleGroupId) {
                        if ( ! isset($groupById[$dto->responsibleGroupId])) {
                            throw new \RuntimeException('Group with id ' . $dto->responsibleGroupId . ' not found');
                        }
                        $dto->responsibleGroup = $groupById[$dto->responsibleGroupId];
                    }
                }
            }
        }
    }


    private function loadAndAttachFiles(array $incidentDTOs): void
    {
        $incidentIds   = [];
        $actionIds     = [];
        $actionTaskIds = [];
        $commentIds    = [];

        /** @var IncidentDTO $incidentDTO */
        foreach ($incidentDTOs as $incidentDTO) {
            $incidentIds[] = $incidentDTO->id;
            foreach ($incidentDTO->actions as $actionDTO) {
                $actionIds[] = $actionDTO->id;

                foreach ($actionDTO->tasks as $actionTaskDTO) {
                    $actionTaskIds[] = $actionTaskDTO->id;
                }

                foreach ($actionDTO->comments as $commentDTO) {
                    $commentIds[] = $commentDTO->id;
                }
            }
        }

        $qb = $this->connection->createQueryBuilder()
                               ->from('ims_files')
                               ->select('ims_files.*')
                               ->andWhere('deleted != true');

        $orX = $qb->expr()->orX();

        $orX->add('ims_files.owner_code = \'incident\' and ims_files.owner_id in (:incidentIds)');
        $qb->setParameter('incidentIds', $incidentIds, Connection::PARAM_INT_ARRAY);

        $orX->add('ims_files.owner_code = \'action\' and ims_files.owner_id in (:actionIds)');
        $qb->setParameter('actionIds', $actionIds, Connection::PARAM_INT_ARRAY);

        $orX->add('ims_files.owner_code = \'action_task_input\' and ims_files.owner_id in (:actionTaskIds)');
        $qb->setParameter('actionTaskIds', $actionTaskIds, Connection::PARAM_INT_ARRAY);

        $orX->add('ims_files.owner_code = \'action_task_report\' and ims_files.owner_id in (:actionTaskIds)');
        $qb->setParameter('actionTaskIds', $actionTaskIds, Connection::PARAM_INT_ARRAY);

        $orX->add('ims_files.owner_code = \'comment\' and ims_files.owner_id in (:commentIds)');
        $qb->setParameter('commentIds', $commentIds, Connection::PARAM_INT_ARRAY);

        $qb->andWhere($orX);

        $stmt = $qb->execute();

        $rows = $stmt->fetchAll();

        $fileDTOs = [];

        foreach ($rows as $row) {
            $dto = FileDTO::fromRow($row);

            // TODO web file
            $dto->path = $this->generator->generate('ims_file_download', [ 'fileId' => $dto->id ]);

            $fileDTOs[$dto->ownerCode][$dto->ownerId][] = $dto;
        }

        /** @var IncidentDTO $incidentDTO */
        foreach ($incidentDTOs as $incidentDTO) {
            $incidentDTO->files = $fileDTOs['incident'][$incidentDTO->id] ?? [];
            foreach ($incidentDTO->actions as $actionDTO) {
                $actionDTO->files = $fileDTOs['action'][$actionDTO->id] ?? [];

                /** @var CommentDTO $commentDTO */
                foreach ($actionDTO->comments as $commentDTO) {
                    $commentDTO->files = $fileDTOs['comment'][$commentDTO->id] ?? [];
                }

                /** @var ActionTaskDTO $actionTaskDTO */
                foreach ($actionDTO->tasks as $actionTaskDTO) {
                    $actionTaskDTO->filesInput = $fileDTOs['action_task_input'][$actionTaskDTO->id] ?? [];
                }

                /** @var ActionTaskDTO $actionTaskDTO */
                foreach ($actionDTO->tasks as $actionTaskDTO) {
                    $actionTaskDTO->filesReport = $fileDTOs['action_task_report'][$actionTaskDTO->id] ?? [];
                }
            }
        }

    }


    private function loadIncidentRefs(array $incidentRefIds): array
    {
        $rows = $this->connection->createQueryBuilder()
                                 ->from('ims_incidents')
                                 ->select('ims_incidents.id, ims_incidents.title, ims_incidents.description, ims_incidents.date')
                                 ->andWhere('ims_incidents.id in (:ids)')
                                 ->setParameter('ids', $incidentRefIds, Connection::PARAM_INT_ARRAY)
                                 ->execute()
                                 ->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['id']] = IncidentReferenceDTO::fromRow($row);
        }

        return $grouped;
    }


    private function loadChildRefsIncidents(array $incidentIds) : array
    {
        $rows = $this->connection->createQueryBuilder()
                                 ->from('ims_incidents')
                                 ->select('ims_incidents.id, ims_incidents.title, ims_incidents.description, ims_incidents.date, ims_incidents.repeated_incident_id')
                                 ->andWhere('ims_incidents.repeated_incident_id in (:ids)')
                                 ->setParameter('ids', $incidentIds, Connection::PARAM_INT_ARRAY)
                                 ->execute()
                                 ->fetchAll(FetchMode::ASSOCIATIVE);

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['repeated_incident_id']][] = IncidentReferenceDTO::fromRow($row);
        }

        return $grouped;
    }
}