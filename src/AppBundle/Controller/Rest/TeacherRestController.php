<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\AcademicYearManagerService;
use AppBundle\Service\TeacherManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TeacherRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/teacher",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class TeacherRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="name", description="Teacher name")
     * @Rest\RequestParam(name="surname", description="Teacher surname")
     * @Rest\RequestParam(name="email", requirements="^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$", description="Teacher email")
     *
     * @ApiDoc(
     *     description="Register a new teacher",
     *     section="Teachers",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when an teacher tith the same name and surname already exists",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var TeacherManagerService $teacherManager */
        $teacherManager = $this->get(TeacherManagerService::SERVICE_NAME);

        $name = $paramFetcher->get('name');
        $surname = $paramFetcher->get('surname');
        $email = $paramFetcher->get('email');

        $teacherManager->createNew($name, $surname, $email);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get teacher by id",
     *     section="Teachers",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the teacher with the given id is not founded",
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
        /** @var TeacherManagerService $teacherManager */
        $teacherManager = $this->get(TeacherManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYear = $teacherManager->getTeacherById($id);

        return new Response(
            $serializer->serialize($academicYear, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\QueryParam(name="name", description="New name")
     * @Rest\QueryParam(name="surname", description="New surname")
     * @Rest\QueryParam(name="email",requirements="^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$", description="New email address")
     *
     * @ApiDoc(
     *     description="Update teacher with the given id",
     *     section="Teachers",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the teacher with the given id is not founded",
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
        /** @var TeacherManagerService $teacherManager */
        $teacherManager = $this->get(TeacherManagerService::SERVICE_NAME);

        $name = $paramFetcher->get('name');
        $surname = $paramFetcher->get('surname');
        $email = $paramFetcher->get('email');


        $teacherManager->updateTeacher($name, $surname, $email);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Remove teacher with a given id",
     *     section="Teachers",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the teacher with the given id is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @return Response
     */
    public function removeAction(int $id)
    {
        /** @var TeacherManagerService $teacherManager */
        $teacherManager = $this->get(TeacherManagerService::SERVICE_NAME);

        $teacherManager->removeTeacherById($id);

        return new Response('removed', Response::HTTP_OK);
    }
}