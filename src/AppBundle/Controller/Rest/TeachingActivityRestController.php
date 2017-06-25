<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Service\ActivityManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use JMS\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TeachingActivityRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/teaching-activity",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class TeachingActivityRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="activityCategory", description="Activity category   ex: course,seminar,exam")
     * @Rest\RequestParam(name="academicYear", description="Academic year name   ex: 2016-2017")
     * @Rest\RequestParam(name="semesterNumber", description="Semester number   ex: 1,2")
     * @Rest\RequestParam(name="weekType", strict=false, default="every", description="Week type   ex: odd, even, every")
     * @Rest\RequestParam(name="day", description="Day   ex: 1-7")
     * @Rest\RequestParam(name="hour", description="Hour   ex: 08-18")
     * @Rest\RequestParam(name="duration", default=2, description="Activity duration in hours ex: 1,2")
     * @Rest\RequestParam(name="teacher", description="Person id that teach this activity ")
     * @Rest\RequestParam(name="subject", description="Subject id related with this activity")
     * @Rest\RequestParam(name="location", description="Location in witch the activity is placed")
     * @Rest\RequestParam(name="participants", description="A list of participants ex: {type:x, id:y}")
     *
     * @ApiDoc(
     *     description="Create a new teaching activity",
     *     section="Activity",
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


        $activityCategory = $paramFetcher->get('activityCategory');
        $academicYearName = $paramFetcher->get('academicYear');
        $weekType = $paramFetcher->get('weekType');
        $semesterNumber = $paramFetcher->get('semesterNumber');
        $day = $paramFetcher->get('day');
        $hour = $paramFetcher->get('hour');
        $duration = $paramFetcher->get('duration');
        $teacherId = $paramFetcher->get('teacher');
        $subjectId = $paramFetcher->get('subject');
        $locationId = $paramFetcher->get('location');
        $participantsId = json_decode($paramFetcher->get('participants'), true);

        $activityManager->createTeachingActivity(
            $activityCategory, $academicYearName, $semesterNumber, $weekType, $day, $hour, $duration, $teacherId, $subjectId, $locationId, $participantsId
        );

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Post("/file/{academicYear}/{semesterNumber}.{_format}")
     *
     * @Rest\RequestParam(name="academicYear", nullable=false, description="Academic year name   ex: 2016-2017")
     * @Rest\RequestParam(name="semesterNumber", description="Semester number   ex: 1,2")
     * @Rest\FileParam(name="file", strict=true, description="Csv file")
     *
     * @ApiDoc(
     *     description="Load all teaching activities for a semester",
     *     section="Activity",
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
    public function csvLoadAction(ParamFetcher $paramFetcher, $academicYear, $semesterNumber)
    {
        /** @var UploadedFile $file */
        $file = $paramFetcher->get('file');

        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);

        $result = $activityManager->loadActivitiesFromCsv($academicYear, $semesterNumber, file_get_contents($file->getRealPath()));

        return new Response('', Response::HTTP_OK);
    }


    /**
     * @Rest\Get("/all/{academicYear}/{semesterNumber}/{specialization}.{_format}")
     *
     * @ApiDoc(
     *     description="Get activities form a semester and specialization",
     *     section="Activity",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $academicYear
     * @param int $semesterNumber
     * @param int $specialization
     * @param $_format
     * @return Response
     * @internal param $date
     */
    public function getAllActivitiesForSpecializationOnSemester(string $academicYear, int $semesterNumber,int $specialization, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $activities = $activityManager->getAllActivitiesForSemesterAndSpecialization($academicYear, $semesterNumber, $specialization);

        return new Response(
            $serializer->serialize($activities, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\RequestParam(name="teacher", nullable=true, allowBlank=true, description="Subject short-name ex: D.A.D.R")
     * @Rest\RequestParam(name="activityCategory", nullable=true, allowBlank=true, description="Activity category   ex: course,seminar,exam")
     * @Rest\RequestParam(name="academicYear", nullable=true, allowBlank=true, description="Academic year name   ex: 2016-2017")
     * @Rest\RequestParam(name="semesterNumber", nullable=true, allowBlank=true, description="Semester number   ex: 1,2")
     * @Rest\RequestParam(name="weekType", strict=false, default="every", nullable=true, allowBlank=true, description="Week type   ex: odd, even, every")
     * @Rest\RequestParam(name="day", nullable=true, allowBlank=true, description="Day   ex: 1-7")
     * @Rest\RequestParam(name="hour", nullable=true, allowBlank=true, description="Hour   ex: 08-18")
     * @Rest\RequestParam(name="duration", nullable=true, allowBlank=true, default=2, description="Activity duration in hours ex: 1,2")
     * @Rest\RequestParam(name="teacher", nullable=true, allowBlank=true, description="Person id that teach this activity ")
     * @Rest\RequestParam(name="subject", nullable=true, allowBlank=true, description="Subject id related with this activity")
     * @Rest\RequestParam(name="location", nullable=true, allowBlank=true, description="Location in witch the activity is placed")
     * @Rest\RequestParam(name="participants", description="A list of participants ex: {123, 143}")
     *
     * @ApiDoc(
     *     description="Update subject",
     *     section="Activities",
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

        $activityManager->updateTeachingActivity($id, $changes);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/all-types.{_format}")
     *
     * @ApiDoc(
     *     description="Get all activity types",
     *     section="Activity",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param $_format
     * @return Response
     */
    public function getAllTeachingActivityTypes($_format)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $activityTypes = array(
            ActivityCategory::COURSE,
            ActivityCategory::SEMINAR,
            ActivityCategory::LABORATORY,
        );

        return new Response(
            $serializer->serialize($activityTypes, $_format),
            Response::HTTP_OK
        );
    }
}