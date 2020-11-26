<?php

namespace ANOITCOM\IMSBundle\Services\Users;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use ANOITCOM\Wiki\Repository\Group\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use ANOITCOM\IMSBundle\Services\PermissionsService\GroupsProvider;

class GroupsService
{

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var GroupsProvider
     */
    private $groupsProvider;


    public function __construct(EntityManagerInterface $em, GroupsProvider $groupsProvider)
    {
        // по лругому не работает
        $this->groupRepository              = $em->getRepository(UserGroup::class);
        $this->groupsProvider = $groupsProvider;
    }


    public function getAllCanBeResponsibleForActionAsOptions(): array
    {
        $groups = $this->groupsProvider->getAllCanBeResponsibleForAction();

        return array_map(function (UserGroup $group) {
            return [
                'id'    => $group->getId(),
                'title' => $group->getTitle()
            ];
        }, $groups);

    }
}