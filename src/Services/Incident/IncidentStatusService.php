<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\Wiki\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ANOITCOM\IMSBundle\Domain\Incident\Status\IncidentStatusList;
use ANOITCOM\IMSBundle\Entity\Incident\Incident;
use ANOITCOM\IMSBundle\Entity\Incident\IncidentStatus;

class IncidentStatusService
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var IncidentStatusList
     */
    private $incidentStatusList;


    public function __construct(EntityManagerInterface $em, IncidentStatusList $incidentStatusList)
    {

        $this->em                 = $em;
        $this->incidentStatusList = $incidentStatusList;
    }


    public function createStatus(Incident $incident, string $statusCode, User $user): IncidentStatus
    {
        if ( ! $this->incidentStatusList->hasByCode($statusCode)) {
            throw new \InvalidArgumentException('Incident status with code ' . $statusCode . ' not found!');
        }

        $now = new \DateTimeImmutable();

        $status = new IncidentStatus();
        $status->setCode($statusCode);
        $status->setCreatedAt($now);
        $status->setCreatedBy($user);
        $status->setIncident($incident);

        $this->em->persist($status);

        $incident->setStatus($status);

        $this->em->flush();

        return $status;
    }
}