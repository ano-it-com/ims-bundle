<?php

namespace ANOITCOM\IMSBundle\Controller\API\Incident;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\ActionForActionsDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\ActionWithCommentDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\AddActionTasksForActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\CreateActionsForIncidentDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\IncidentActionDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\SetActionTaskStatusDTO;
use ANOITCOM\IMSBundle\Controller\API\Incident\RequestDTO\UpdateActionTaskReportDTO;
use ANOITCOM\IMSBundle\Entity\Incident\Action\Action;
use ANOITCOM\IMSBundle\Infrastructure\Response\ResponseFactory;
use ANOITCOM\IMSBundle\Services\Incident\IncidentActionsService;

class IncidentActionsAPIController extends AbstractController
{

    /**
     * @var IncidentActionsService
     */
    private $incidentActionsService;


    public function __construct(
        IncidentActionsService $incidentActionsService
    ) {

        $this->incidentActionsService = $incidentActionsService;
    }


    /**
     * @Route("/incident/action/to-confirmation", name="ims_incident_action_to_confirmation", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function toConfirmation(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->toConfirmation($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/to-work", name="ims_incident_action_to_work", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function toWork(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->toWork($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/confirm", name="ims_incident_action_confirm", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function confirm(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->confirm($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/reject", name="ims_incident_action_reject", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function reject(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->reject($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/close", name="ims_incident_action_close", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function close(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->close($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/take-in-work", name="ims_incident_action_take_in_work", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function takeInWorkAsResponsibleUser(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->takeInWorkAsResponsibleUser($actionForActionsDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/to-clarification", name="ims_incident_action_to_clarification", methods={"POST"})
     *
     * @param ActionWithCommentDTO $actionWithCommentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function toClarification(ActionWithCommentDTO $actionWithCommentDTO): JsonResponse
    {
        $this->incidentActionsService->toClarification($actionWithCommentDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/to-approving", name="ims_incident_action_to_approving", methods={"POST"})
     *
     * @param ActionForActionsDTO $actionForActionsDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function toApproving(ActionForActionsDTO $actionForActionsDTO): JsonResponse
    {
        $this->incidentActionsService->toApproving($actionForActionsDTO);

        return ResponseFactory::success([]);

    }


    /**
     * @Route("/incident/action/to-correction", name="ims_incident_action_to_correction", methods={"POST"})
     *
     * @param ActionWithCommentDTO $actionWithCommentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function toCorrection(ActionWithCommentDTO $actionWithCommentDTO): JsonResponse
    {
        $this->incidentActionsService->toCorrection($actionWithCommentDTO);

        return ResponseFactory::success([]);

    }


    /**
     * @Route("/incident/action/back-from-clarification", name="ims_incident_action_from_clarification", methods={"POST"})
     *
     * @param ActionWithCommentDTO $actionWithCommentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function backFromClarification(ActionWithCommentDTO $actionWithCommentDTO): JsonResponse
    {
        $this->incidentActionsService->backFromClarification($actionWithCommentDTO);

        return ResponseFactory::success([]);

    }


    /**
     * @Route("/incident/close", name="ims_incident_close", methods={"POST"})
     *
     * @param IncidentActionDTO $incidentActionDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function closeIncident(IncidentActionDTO $incidentActionDTO): JsonResponse
    {
        $this->incidentActionsService->closeIncident($incidentActionDTO);

        return ResponseFactory::success([]);

    }


    /**
     * @Route("/incident/action/add", name="ims_incident_action_add", methods={"POST"})
     *
     * @param CreateActionsForIncidentDTO $createActionsForIncidentDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addActionsToIncident(CreateActionsForIncidentDTO $createActionsForIncidentDTO): JsonResponse
    {
        $actions = $this->incidentActionsService->addActionsToIncident($createActionsForIncidentDTO);

        $ids = array_map(static function (Action $action) {
            return $action->getId();
        }, $actions);

        return ResponseFactory::success($ids);

    }


    /**
     * @Route("/incident/action/task/set-status", name="ims_incident_action_task_set_status", methods={"POST"})
     *
     * @param SetActionTaskStatusDTO $setActionTaskStatusDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function setActionTaskStatus(SetActionTaskStatusDTO $setActionTaskStatusDTO): JsonResponse
    {
        $this->incidentActionsService->setActionTaskStatus($setActionTaskStatusDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/task/update-report", name="ims_incident_action_task_update_report", methods={"POST"})
     *
     * @param UpdateActionTaskReportDTO $updateActionTaskDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function updateActionTaskReport(UpdateActionTaskReportDTO $updateActionTaskDTO): JsonResponse
    {
        $this->incidentActionsService->updateActionTaskReport($updateActionTaskDTO);

        return ResponseFactory::success([]);
    }


    /**
     * @Route("/incident/action/task/add", name="ims_incident_action_task_add", methods={"POST"})
     *
     * @param AddActionTasksForActionDTO $addActionTaskToActionDTO
     *
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addActionTaskToAction(AddActionTasksForActionDTO $addActionTaskToActionDTO): JsonResponse
    {
        $this->incidentActionsService->addActionTaskToAction($addActionTaskToActionDTO);

        return ResponseFactory::success([]);

    }
}
