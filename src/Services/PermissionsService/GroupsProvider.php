<?php

namespace ANOITCOM\IMSBundle\Services\PermissionsService;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class GroupsProvider
{

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;

    /**
     * @var \ANOITCOM\Wiki\Repository\Group\GroupRepository
     */
    private $groupRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;


    public function __construct(EntityManagerInterface $em, PermissionsProvider $permissionsProvider)
    {

        $this->groupRepository     = $em->getRepository(UserGroup::class);
        $this->permissionsProvider = $permissionsProvider;
        $this->em                  = $em;
    }


    public function getAllCanBeResponsibleForAction(): array
    {
        $permission  = 'is_executor';
        $permission2 = 'ims_front_group';

        $stmt = $this->em->getConnection()
                         ->createQueryBuilder()
                         ->select('groups.id')
                         ->from('groups')
                         ->leftJoin('groups', 'ims_group_permissions', 'ims_gp', 'groups.id = ims_gp.group_id')
                         ->leftJoin('groups', 'ims_group_permissions', 'ims_gp2', 'groups.id = ims_gp2.group_id')
                         ->leftJoin('ims_gp', 'ims_permissions', 'ip', 'ims_gp.permission_id = ip.id')
                         ->leftJoin('ims_gp2', 'ims_permissions', 'ip2', 'ims_gp2.permission_id = ip2.id')
                         ->andWhere('ip.code = :permissionCode1')
                         ->andWhere('ip2.code = :permissionCode2')
                         ->setParameter('permissionCode1', $permission)
                         ->setParameter('permissionCode2', $permission2)->execute();

        $groupsRows = $stmt->fetchAll();

        $groupIds = array_column($groupsRows, 'id');

        return $this->groupRepository
            ->createQueryBuilder('g')
            ->where('g.id in (:ids)')
            ->orderBy('g.title', 'asc')
            ->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();


    }

    public function getAllCanProcessWithoutModeration(): array
    {
        $permission  = 'work_without_moderation';
        $permission2 = 'ims_front_group';

        $stmt = $this->em->getConnection()
                         ->createQueryBuilder()
                         ->select('groups.id')
                         ->from('groups')
                         ->leftJoin('groups', 'ims_group_permissions', 'ims_gp', 'groups.id = ims_gp.group_id')
                         ->leftJoin('groups', 'ims_group_permissions', 'ims_gp2', 'groups.id = ims_gp2.group_id')
                         ->leftJoin('ims_gp', 'ims_permissions', 'ip', 'ims_gp.permission_id = ip.id')
                         ->leftJoin('ims_gp2', 'ims_permissions', 'ip2', 'ims_gp2.permission_id = ip2.id')
                         ->andWhere('ip.code = :permissionCode1')
                         ->andWhere('ip2.code = :permissionCode2')
                         ->setParameter('permissionCode1', $permission)
                         ->setParameter('permissionCode2', $permission2)->execute();

        $groupsRows = $stmt->fetchAll();

        $groupIds = array_column($groupsRows, 'id');

        return $this->groupRepository
            ->createQueryBuilder('g')
            ->where('g.id in (:ids)')
            ->orderBy('g.title', 'asc')
            ->setParameter('ids', $groupIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getResult();


    }


    public function getOneForPermission(string $permissionCode): ?UserGroup
    {
        $stmt = $this->em->getConnection()
                         ->createQueryBuilder()
                         ->select('groups.id')
                         ->from('groups')
                         ->leftJoin('groups', 'ims_group_permissions', 'ims_gp', 'groups.id = ims_gp.group_id')
                         ->leftJoin('ims_gp', 'ims_permissions', 'ip', 'ims_gp.permission_id = ip.id')
                         ->andWhere('ip.code = :permissionCode')
                         ->setParameter('permissionCode', $permissionCode)
                         ->execute();

        $groupsRows = $stmt->fetchAll();

        $ids = array_column($groupsRows, 'id');

        if (count($ids) > 1) {
            throw new NonUniqueResultException('Unique constraint for group with permission ' . $permissionCode);
        }

        if ( ! count($ids)) {
            return null;
        }

        $id = reset($ids);

        return $this->groupRepository->find($id);

    }


    public function getSupervisorGroup(): UserGroup
    {
        return $this->getGroupForPermission('is_supervisor');
    }


    public function getModeratorGroup(): UserGroup
    {
        return $this->getGroupForPermission('is_moderator');

    }


    private function getGroupForPermission(string $permissionCode): UserGroup
    {
        try {
            $group = $this->getOneForPermission($permissionCode);
        } catch (NonUniqueResultException $e) {
            throw new \InvalidArgumentException('Group for permission code ' . $permissionCode . ' must be unique!');
        }

        if ( ! $group) {
            throw new \InvalidArgumentException('Unique Group not found for permission code ' . $permissionCode);
        }

        return $group;
    }

}