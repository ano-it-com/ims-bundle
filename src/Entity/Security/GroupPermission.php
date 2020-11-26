<?php

namespace ANOITCOM\IMSBundle\Entity\Security;

use ANOITCOM\Wiki\Entity\Groups\UserGroup;
use Doctrine\ORM\Mapping as ORM;
use ANOITCOM\IMSBundle\Repository\Security\GroupPermissionRepository;

/**
 * @ORM\Entity(repositoryClass=GroupPermissionRepository::class)
 * @ORM\Table(name="ims_group_permissions",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="group_permission_unique",
 *            columns={"group_id", "permission_id"})
 *    }
 * )
 */
class GroupPermission
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=UserGroup::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity=Permission::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $permission;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb": true})
     */
    private $restriction = [];


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getGroup(): UserGroup
    {
        return $this->group;
    }


    public function setGroup(UserGroup $group): self
    {
        $this->group = $group;

        return $this;
    }


    public function getPermission(): Permission
    {
        return $this->permission;
    }


    public function setPermission(Permission $permission): self
    {
        $this->permission = $permission;

        return $this;
    }


    public function getRestriction(): ?array
    {
        return $this->restriction;
    }


    public function setRestriction(?array $restriction): self
    {
        $this->restriction = $restriction;

        return $this;
    }

}