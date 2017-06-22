<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\AcademicYearManagerService;
use AppBundle\Service\StudentManagerService;
use AppBundle\Service\TeacherManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StudentRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/student",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class StudentRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="name", description="Student name")
     * @Rest\RequestParam(name="surname", description="Student surname")
     * @Rest\RequestParam(name="email", requirements="(.+)@(.+){2,}\.(.+){2,}", description="Student email")
     * @Rest\RequestParam(name="sub-series", description="Student subseries")
     *
     * @ApiDoc(
     *     description="Register a new student",
     *     section="Students",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when an student with the same name and surname already exists",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var StudentManagerService $studentManagerService */
        $studentManagerService = $this->get(StudentManagerService::SERVICE_NAME);

        $name = $paramFetcher->get('name');
        $surname = $paramFetcher->get('surname');
        $email = $paramFetcher->get('email');
        $subSeriesId = $paramFetcher->get('sub-series');

        $studentManagerService->createNew($name, $surname, $email, $subSeriesId);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get student by id",
     *     section="Students",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the student with the given id is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param $id
     * @param string $_format
     * @return Response
     */
    public function getByIdAction($id, $_format)
    {
        /** @var StudentManagerService $studentManagerService */
        $studentManagerService = $this->get(StudentManagerService::SERVICE_NAME);

        $serializer = $this->get('serializer');

        $student = $studentManagerService->getStudentDetailsById($id);

        return new Response(
            $serializer->serialize($student, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post("/email.{_format}")
     *
     * @Rest\RequestParam(name="email", description="Student email")
     *
     * @ApiDoc(
     *     description="Get student by email",
     *     section="Students",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the student with the given email is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $email
     * @param string $_format
     * @return Response
     */
    public function getByEmailAction(ParamFetcher $paramFetcher, $_format)
    {
        /** @var StudentManagerService $studentManagerService */
        $studentManagerService = $this->get(StudentManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $email = $paramFetcher->get('email');

        $student = $studentManagerService->getStudentDetailsByEmail($email);

        return new Response(
            $serializer->serialize($student, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\QueryParam(name="name", description="New name")
     * @Rest\QueryParam(name="surname", description="New surname")
     * @Rest\QueryParam(name="email",requirements="(.+)@(.+){2,}\.(.+){2,}", description="New email address")
     * @Rest\QueryParam(name="sub-series", description="New subSeries")
     *
     * @ApiDoc(
     *     description="Update student with the given id",
     *     section="Students",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the student with the given id is not founded",
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
        /** @var StudentManagerService $studentManagerService */
        $studentManagerService = $this->get(StudentManagerService::SERVICE_NAME);

        $name = $paramFetcher->get('name');
        $surname = $paramFetcher->get('surname');
        $email = $paramFetcher->get('email');
        $subSeries = $paramFetcher->get('sub-series');

        $studentManagerService->updateStudent($id, $name, $surname, $email, (int) $subSeries);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Remove student with a given id",
     *     section="Students",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the student with the given id is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @return Response
     */
    public function removeAction(int $id)
    {
        /** @var StudentManagerService $studentManagerService */
        $studentManagerService = $this->get(StudentManagerService::SERVICE_NAME);

        $studentManagerService->removeStudentById($id);

        return new Response('removed', Response::HTTP_OK);
    }
}