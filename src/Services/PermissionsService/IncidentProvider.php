<?php

namespace ANOITCOM\IMSBundle\Services\PermissionsService;

use ANOITCOM\Wiki\Entity\User;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Repository\Incident\IncidentRepository;
use ANOITCOM\IMSBundle\Services\Incident\Provider\IncidentDTOProvider;

class IncidentProvider
{

    /**
     * @var IncidentRepository
     */
    private $incidentRepository;

    /**
     * @var IncidentDTOProvider
     */
    private $incidentDTOProvider;


    public function __construct(IncidentRepository $incidentRepository, IncidentDTOProvider $incidentDTOProvider)
    {

        $this->incidentRepository  = $incidentRepository;
        $this->incidentDTOProvider = $incidentDTOProvider;
    }


    public function getById(int $incidentId, User $user): ?Incident
    {
        $canAccess = $this->incidentDTOProvider->canAccess($incidentId, $user);
        if ( ! $canAccess) {
            return null;
        }

        return $this->incidentRepository->find($incidentId);
    }

}