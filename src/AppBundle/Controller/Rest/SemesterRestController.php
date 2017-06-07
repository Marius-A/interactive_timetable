<?php


namespace AppBundle\Controller\Rest;


use AppBundle\Service\AcademicYearManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SemesterRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/semester",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class SemesterRestController extends FOSRestController
{
    /**
     * @Rest\Get("/{semesterId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get semester by id",
     *     section="Semester",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the semester is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $semesterId
     * @param string $_format
     * @return Response
     */
    public function getByIdAction($semesterId, $_format)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYear = $academicYearManager->getSemesterById($semesterId);


        return new Response(
            $serializer->serialize($academicYear, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/{academicYearId}/{semesterNumber}.{_format}")
     *
     * @ApiDoc(
     *     description="Get semester by id",
     *     section="Semester",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the semester is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $academicYearId
     * @param int $semesterNumber
     * @param string $_format
     * @return Response
     */
    public function getByIdAcademicYearAndSemesterNumber($academicYearId, $semesterNumber, $_format)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYear = $academicYearManager->getAcademicYearById($academicYearId);
        $academicYearManager->throwNotFoundExceptionIfIsAcademicYearIsNullNull($academicYear);

        $semester = $academicYearManager->getSemesterByAcademicYearAndNumber($academicYear, $semesterNumber);

        return new Response(
            $serializer->serialize($semester, $_format),
            Response::HTTP_OK
        );
    }
}