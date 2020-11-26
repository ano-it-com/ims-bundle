<?php

namespace ANOITCOM\IMSBundle\Services\Incident;

use ANOITCOM\IMSBundle\Entity\Location\Location;
use ANOITCOM\IMSBundle\Repository\Location\LocationRepository;

class LocationService
{

    /**
     * @var LocationRepository
     */
    private $locationRepository;


    public function __construct(LocationRepository $locationRepository)
    {

        $this->locationRepository = $locationRepository;
    }


    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return []
     */
    public function findByAsOptions(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        $entities = $this->locationRepository->findBy($criteria, $orderBy, $limit, $offset);

        return array_map(function (Location $location) {
            return [
                'id'    => $location->getId(),
                'title' => $location->getTitle()
            ];
        }, $entities);
    }
}