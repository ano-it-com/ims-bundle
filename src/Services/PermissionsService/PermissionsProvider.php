<?php

namespace ANOITCOM\IMSBundle\Services\PermissionsService;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PermissionsProvider
{

    /**
     * @var EntityManagerInterface
     */
    private $em;


    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }


    public function getStatusRestrictions(string $permissionCode, User $user): array
    {
        $stmt = $this->em->getConnection()->createQueryBuilder()
                         ->from('ims_group_permissions')->select('ims_group_permissions.restriction')
                         ->leftJoin('ims_group_permissions', 'ims_permissions', 'ims_permissions', 'ims_group_permissions.permission_id = ims_permissions.id')
                         ->rightJoin('ims_group_permissions', 'groups', 'groups', 'ims_group_permissions.group_id = groups.id')
                         ->leftJoin('groups', 'users_groups', 'users_groups', 'users_groups.group_id = groups.id')
                         ->andWhere('users_groups.user_id = :userId')
                         ->andWhere('ims_group_permissions.restriction is not null')
                         ->andWhere('ims_permissions.code = :permissionsCode')
                         ->setParameters([
                             'userId'          => $user->getId(),
                             'permissionsCode' => $permissionCode,
                         ])->execute();

        $restrictions = $stmt->fetchAll();

        $joinedRestrictions = [];

        foreach ($restrictions as $row) {
            try {
                $restriction = json_decode($row['restriction'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($restriction as $status => $allow) {
                if (isset($joinedRestrictions[$status]) && $joinedRestrictions[$status]) {
                    continue;
                }
                if ($allow) {
                    $joinedRestrictions[$status] = true;
                }
            }
        }

        return $joinedRestrictions;
    }


    public function userHasPermission(string $permissionCode, User $user): bool
    {
        $stmt = $this->em->getConnection()->createQueryBuilder()
                         ->from('ims_group_permissions')->select('count(*)')
                         ->leftJoin('ims_group_permissions', 'ims_permissions', 'ims_permissions', 'ims_group_permissions.permission_id = ims_permissions.id')
                         ->rightJoin('ims_group_permissions', 'groups', 'groups', 'ims_group_permissions.group_id = groups.id')
                         ->leftJoin('groups', 'users_groups', 'users_groups', 'users_groups.group_id = groups.id')
                         ->andWhere('users_groups.user_id = :userId')
                         ->andWhere('ims_permissions.code = :permissionsCode')
                         ->setParameters([
                             'userId'          => $user->getId(),
                             'permissionsCode' => $permissionCode,
                         ])->execute();

        $count = $stmt->fetchColumn(0);

        return $count > 0;


    }


    public function groupHasPermission(string $permissionCode, UserGroup $group): bool
    {
        $stmt = $this->em->getConnection()->createQueryBuilder()
                         ->from('ims_group_permissions')->select('count(*)')
                         ->leftJoin('ims_group_permissions', 'groups', 'groups', 'ims_group_permissions.group_id = groups.id')
                         ->leftJoin('ims_group_permissions', 'ims_permissions', 'ims_permissions', 'ims_group_permissions.permission_id = ims_permissions.id')
                         ->andWhere('groups.id = :groupId')
                         ->andWhere('ims_permissions.code = :permissionsCode')
                         ->setParameters([
                             'groupId'         => $group->getId(),
                             'permissionsCode' => $permissionCode,
                         ])->execute();

        $count = $stmt->fetchColumn(0);

        return $count > 0;
    }


    public function getAllNonRestrictedPermissions(User $user): array
    {
        $stmt = $this->em->getConnection()->createQueryBuilder()
                         ->from('ims_permissions')->select('ims_permissions.code')
                         ->leftJoin('ims_permissions', 'ims_group_permissions', 'ims_group_permissions', 'ims_group_permissions.permission_id = ims_permissions.id')
                         ->leftJoin('ims_group_permissions', 'users_groups', 'users_groups', 'ims_group_permissions.group_id = users_groups.group_id')
                         ->andWhere('users_groups.user_id = :userId')
                         ->andWhere('ims_group_permissions.restriction is null')
                         ->setParameters([
                             'userId' => $user->getId(),
                         ])->execute();

        $rows = $stmt->fetchAll();

        return array_column($rows, 'code');
    }
}