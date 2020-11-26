<?php

namespace ANOITCOM\IMSBundle\Controller;

use ANOITCOM\Wiki\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusNew;
use ANOITCOM\IMSBundle\Services\PermissionsService\PermissionsProvider;

/**
 * Class SPAController
 * @package ANOITCOM\IMSBundle\Controller
 * @IsGranted("ROLE_USER")
 */
class SPAController extends AbstractController
{

    /**
     * @var PermissionsProvider
     */
    private $permissionsProvider;


    public function __construct(PermissionsProvider $permissionsProvider)
    {
        $this->permissionsProvider = $permissionsProvider;
    }


    /**
     * @Route("/{jsRouting}", name="ims_spa", requirements={"jsRouting"=".+"}, defaults={"jsRouting": null})
     */
    public function index(string $jsRouting)
    {

        /** @var User $user */
        $user = $this->getUser();

        //TODO - rewrite
        $canNotAccess = true;
        foreach ($user->getGroups() as $group) {
            if (in_array($group->getTitle(), [
                'Исполнитель',
                'Супервизор',
                'Модератор',
            ], true)) {
                $canNotAccess = false;
                break;
            }
        }

        if ($canNotAccess) {
            throw new AccessDeniedHttpException();
        }

        $incidentEditRestrictions = $this->permissionsProvider->getStatusRestrictions('can_edit_incident_by_status', $user);
        $canCreate                = $incidentEditRestrictions[IncidentStatusNew::getCode()] ?? false;

        $permissions                      = $this->permissionsProvider->getAllNonRestrictedPermissions($user);
        $canViewResponsibleUser           = ! (in_array('is_moderator', $permissions, true) || in_array('is_supervisor', $permissions, true));
        $canViewAuthorAndResponsibleGroup = ! in_array('is_executor', $permissions, true);

        $rights = [
            'canCreateIncident'                => $canCreate,
            'canViewResponsibleUser'           => $canViewResponsibleUser,
            'canViewAuthorAndResponsibleGroup' => $canViewAuthorAndResponsibleGroup,
        ];

        return $this->render('ims/index.html.twig', [ 'rights' => $rights ]);
    }
}