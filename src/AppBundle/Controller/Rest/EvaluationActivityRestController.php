<?php


namespace AppBundle\Controller\Rest;


use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Service\ActivityManagerService;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EvaluationActivityRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/evaluation-activity",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class EvaluationActivityRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="activityCategory", description="Activity category   ex: exam,coloquim,exam")
     * @Rest\RequestParam(name="academicYear", description="Academic year name   ex: 2016-2017")
     * @Rest\RequestParam(name="type", description="Examen sau restanta")
     * @Rest\RequestParam(name="date", description="Date   ex: 24.04.2017")
     * @Rest\RequestParam(name="hour", description="Hour   ex: 08-18")
     * @Rest\RequestParam(name="duration", default=2, description="Activity duration in hours ex: 1,2")
     * @Rest\RequestParam(name="teacher", description="Person id that supervised this activity ")
     * @Rest\RequestParam(name="subject", description="Subject id related with this activity")
     * @Rest\RequestParam(name="location", description="Location in witch the activity is placed")
     * @Rest\RequestParam(name="participants", description="A list of participants ex: {type:x, id:y}")
     *
     * @ApiDoc(
     *     description="Create a new evaluation activity",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);

        $academicYearName = $paramFetcher->get('academicYear');
        $activityCategory = $paramFetcher->get('activityCategory');
        $date = new \DateTime($paramFetcher->get('date'));
        $hour = $paramFetcher->get('hour');
        $type = strtolower($paramFetcher->get('type'));
        $duration = $paramFetcher->get('duration');
        $teacherId = $paramFetcher->get('teacher');
        $subjectId = $paramFetcher->get('subject');
        $locationId = $paramFetcher->get('location');
        $participantsId = json_decode($paramFetcher->get('participants'), true);


        $activityManager->createAndPersistEvaluationActivity(
            $activityCategory, $type, $academicYearName, $locationId, $subjectId, $teacherId, $date, $hour, $duration, $participantsId
        );

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\RequestParam(name="activityCategory", nullable=true, allowBlank=true, description="Activity category   ex: course,seminar,exam")
     * @Rest\RequestParam(name="type", nullable=true, allowBlank=true, description="Examen sau restanta")
     * @Rest\RequestParam(name="academicYear", nullable=true, allowBlank=true, description="Academic year name   ex: 2016-2017")
     * @Rest\RequestParam(name="date", strict=false, nullable=true, allowBlank=true, description="date")
     * @Rest\RequestParam(name="hour", nullable=true, allowBlank=true, description="Hour   ex: 08-18")
     * @Rest\RequestParam(name="duration", nullable=true, allowBlank=true, default=2, description="Activity duration in hours ex: 1,2")
     * @Rest\RequestParam(name="teacher", nullable=true, allowBlank=true, description="Person id that teach this activity ")
     * @Rest\RequestParam(name="subject", nullable=true, allowBlank=true, description="Subject id related with this activity")
     * @Rest\RequestParam(name="location", nullable=true, allowBlank=true, description="Location in witch the activity is placed")
     * @Rest\RequestParam(name="participants",  nullable=true, allowBlank=true, description="A list of participants ex: {123, 143}")
     *
     * @ApiDoc(
     *     description="Update evaluation activity",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when subject was not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function updateAction(int $id, ParamFetcher $paramFetcher)
    {

        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);

        $changes = $paramFetcher->all();

        $activityManager->updateEvaluationActivity($id, $changes);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/all-types.{_format}")
     *
     * @ApiDoc(
     *     description="Get all evaluation-activity types",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param $_format
     * @return Response
     */
    public function getAllEvaluationActivitiesTypes($_format)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $activityTypes = array(
            ActivityCategory::EXAM,
            ActivityCategory::COLLOQUIUM,
            ActivityCategory::PROJECT_PRESENTATION,
        );

        return new Response(
            $serializer->serialize($activityTypes, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/all/{academicYear}/{specialization}/{type}.{_format}")
     *
     * @ApiDoc(
     *     description="Get all activities form the 'academic year' of type: 'type' from specialization 'specialization'",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when not found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $academicYear
     * @param int $specialization
     * @param $_format
     * @return Response
     */
    public function getAllActivitiesForSpecializationOnSemester(string $academicYear, int $specialization, $type, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $activities = $activityManager->getAllEvaluationActivitiesByAcademicYearAndSpecialization($academicYear, $specialization, $type);

        return new Response(
            $serializer->serialize($activities, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post("/file/{academicYear}/{evaluationActivityType}.{_format}")
     *
     * @Rest\FileParam(name="file", strict=true, description="Csv file")
     *
     * @ApiDoc(
     *     description="Load all evaluation activities",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @param $academicYear
     * @param $semesterNumber
     * @return Response
     */
    public function csvLoadAction(ParamFetcher $paramFetcher, $academicYear, $evaluationActivityType)
    {
        /** @var UploadedFile $file */
        $file = $paramFetcher->get('file');

        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);

        $activityManager->loadEvaluationActivitiesFromCsv($academicYear, $evaluationActivityType, file_get_contents($file->getRealPath()));

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get evaluation activity by id",
     *     section="Evaluation-Activities",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @param $_format
     * @return Response
     * @internal param $date
     */
    public function getActivityById(int $id, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');


        /** @var $activity*/
        $activity = $activityManager->getEvaluationActivityDetailsById($id);
        return new Response(
            $serializer->serialize($activity, $_format),
            Response::HTTP_OK
        );
    }

}