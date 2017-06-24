<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Activity;
use AppBundle\Model\NodeEntity\TeachingActivity;
use AppBundle\Service\ActivityManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use GraphAware\Neo4j\OGM\Common\Collection;
use JMS\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ActivityRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/activity",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class ActivityRestController extends FOSRestController
{
    /**
     * @Rest\Get("/participant/{participantId}/{yearOfStudy}/{dateTime}.{_format}")
     *
     * @ApiDoc(
     *     description="Get activities",
     *     section="Activity",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $participantId
     * @param int $yearOfStudy
     * @param $dateTime
     * @param $_format
     * @return Response
     * @internal param $date
     */
    public function getActivitiesForParticipantOnDate(int $participantId, int $yearOfStudy, $dateTime, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $date = new \DateTime($dateTime);

        /** @var TeachingActivity[] | Collection $activities */
        $activities = $activityManager->getActivitiesForParticipantOnDate($participantId, $yearOfStudy, $date);


        return new Response(
            $serializer->serialize($activities, 'json'),
            Response::HTTP_OK
        );
    }


    /**
     * @Rest\Get("/student/{studentId}/{dateTime}.{_format}")
     *
     * @ApiDoc(
     *     description="Get activities for student",
     *     section="Activity",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $studentId
     * @param $dateTime
     * @param $_format
     * @return Response
     * @internal param $date
     */
    public function getActivitiesForStudentOnDate(int $studentId, $dateTime, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $date = new \DateTime($dateTime);

        /** @var Activity[] | Collection $activities */
        $activities = $activityManager->getAllWeekActivitiesForStudentOnDate($studentId, $date);

        return new Response(
            $serializer->serialize($activities, 'json'),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/teacher/{teacherId}/{dateTime}.{_format}")
     *
     * @ApiDoc(
     *     description="Get activities for student",
     *     section="Activity",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when ....",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $teacherId
     * @param $dateTime
     * @param $_format
     * @return Response
     * @internal param $date
     */
    public function getActivitiesForTeacherOnDate(int $teacherId, $dateTime, $_format)
    {
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $date = new \DateTime($dateTime);

        /** @var TeachingActivity[] | Collection $activities */
        $activities = $activityManager->getActivitiesForTeacherOnDate($teacherId, $date);
        return new Response(
            $serializer->serialize($activities, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get activity by id",
     *     section="Activity",
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
        $activity = $activityManager->getActivityDetailsById($id);
        return new Response(
            $serializer->serialize($activity, $_format),
            Response::HTTP_OK
        );
    }
}