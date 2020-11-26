<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident;

use ANOITCOM\Wiki\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateIncidentDTO;
use ANOITCOM\IMSBundle\Controller\API\RequestDTO\ListParamsDTO;
use ANOITCOM\IMSBundle\Infrastructure\Response\ResponseFactory;
use ANOITCOM\IMSBundle\Services\Incident\IncidentService;
use ANOITCOM\IMSBundle\UI\Tables\IncidentsTable\IncidentsTable;

class IncidentAPIController extends AbstractController
{

    /**
     * @var IncidentService
     */
    private $incidentService;

    /**
     * @var Security
     */
    private $security;


    public function __construct(
        IncidentService $incidentService,
        Security $security
    ) {

        $this->incidentService = $incidentService;
        $this->security        = $security;
    }


    /**
     * @Route("/incident/create", name="ims_incident_create", methods={"POST"})
     * @param CreateIncidentDTO $createIncidentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createIncident(CreateIncidentDTO $createIncidentDTO): JsonResponse
    {

        $incident = $this->incidentService->createIncident($createIncidentDTO);

        return ResponseFactory::success([ 'id' => $incident->getId() ]);
    }


    /**
     * @Route("/incident/{incidentId}", name="ims_incident_update", methods={"PUT"})
     * @param CreateIncidentDTO $createIncidentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function updateIncident(int $incidentId, CreateIncidentDTO $createIncidentDTO): JsonResponse
    {

        $incident = $this->incidentService->updateIncident($incidentId, $createIncidentDTO);

        return ResponseFactory::success([ 'id' => $incident->getId() ]);
    }


    /**
     * @Route("/incident/meta", name="ims_incident_meta", methods={"GET"})
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function getIncidentMeta(): JsonResponse
    {
        $meta = $this->incidentService->getIncidentMeta();

        return ResponseFactory::success($meta);
    }


    /**
     * @Route("/incident/list", name="ims_incident_list", methods={"GET"})
     * @param ListParamsDTO  $listParamsDTO
     *
     * @param IncidentsTable $table
     *
     * @return JsonResponse
     */
    public function listIncident(ListParamsDTO $listParamsDTO, IncidentsTable $table): JsonResponse
    {
        $tableDataDTO = $table->handle($listParamsDTO);

        return ResponseFactory::success($tableDataDTO->toArray());
    }


    /**
     * @Route("/incident/{incidentId}", name="ims_incident_one", requirements={"incidentId"="\d+"}, methods={"GET"})
     * @param int $incidentId
     *
     * @return JsonResponse
     */
    public function oneIncident(int $incidentId): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ( ! $user) {
            throw new AccessDeniedHttpException();
        }
        $dto = $this->incidentService->getIncidentByIdAsDTO($incidentId, $user);

        if (null === $dto) {
            throw new NotFoundHttpException();
        }

        return ResponseFactory::success((array)$dto);
    }


    /**
     * @Route("/incident/search", name="ims_incident_search", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ( ! $user) {
            throw new AccessDeniedHttpException();
        }

        $searchString = $request->query->get('query');

        $options = $this->incidentService->getByTitleOptions($searchString, $limit = 20, $user);

        return ResponseFactory::success($options);
    }

}