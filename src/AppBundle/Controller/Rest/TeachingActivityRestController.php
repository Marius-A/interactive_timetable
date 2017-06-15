<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\ActivityManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
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
     * @Rest\Post("/file.{_format}")
     *
     * @Rest\RequestParam(name="academicYear", description="Academic year name   ex: 2016-2017")
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
     * @return Response
     */
    public function csvLoadAction(ParamFetcher $paramFetcher)
    {
        /** @var UploadedFile $file */
        $file = $paramFetcher->get('file');
        $academicYearName = $paramFetcher->get('academicYear');
        $semesterNumber = $paramFetcher->get('semesterNumber');

        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->get(ActivityManagerService::SERVICE_NAME);


//        echo($file->guessClientExtension());die;
//
//        if(strtolower($file->getExtension()) != 'csv'){
//            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid file extension:'.$file->getExtension().' extected csv');
//        }

        $activityManager->loadActivitiesFromCsv($academicYearName, $semesterNumber, file_get_contents($file->getRealPath()));


        return new Response('created', Response::HTTP_CREATED);
    }
}