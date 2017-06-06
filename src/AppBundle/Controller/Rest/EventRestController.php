<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Location;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Service\EventManagerService;
use AppBundle\Service\SpecializationManagerService;
use AppBundle\Service\SubjectManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/event",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class EventRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create/{_format}")
     *
     * @Rest\RequestParam(name="location", description="Event location")
     * @Rest\RequestParam(name="description", description="Event description")
     * @Rest\RequestParam(name="name", description="Event name")
     * @Rest\RequestParam(name="start_date", description="Event start date")
     * @Rest\RequestParam(name="end_date", description="Event end date")
     * @Rest\RequestParam(name="recurrence_frequency", description="Event recurrence frequency")
     * @Rest\RequestParam(name="recurrence_interval", description="Event recurrence interval")
     * @Rest\RequestParam(name="recurrence_days", description="Event recurrence days")
     *
     * @ApiDoc(
     *     description="Create a new event",
     *     section="Events",
     *     statusCodes={
     *         201="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
       $eventsManagerService = $this->get(EventManagerService::SERVICE_NAME);

        $location = new Location($paramFetcher->get('location'));
        $description = $paramFetcher->get('description');
        $name = $paramFetcher->get('name');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');
        $recurrenceFrequency = $paramFetcher->get('recurrence_frequency');
        $recurrenceInterval = $paramFetcher->get('recurrence_interval');
        

        return new Response('created', Response::HTTP_CREATED);
    }


    /**
     * @Rest\Get("/get/id/{subjectId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get specialization by id",
     *     section="Subjects",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when subject cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $subjectId
     * @param $_format
     * @return Response
     */
    public function getByIdAction(int $subjectId, $_format)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $specialization = $subjectManager->getSubjectById($subjectId);

        return new Response(
            $serializer->serialize($specialization, $_format),
            Response::HTTP_OK);
    }
}