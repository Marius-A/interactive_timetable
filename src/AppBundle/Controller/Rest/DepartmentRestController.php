<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\DepartmentManagerService;
use AppBundle\Service\FacultyManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DepartmentRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/department",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class DepartmentRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create/{facultyId}.{_format}")
     *
     * @Rest\RequestParam(name="full_name", default=null, description="Department full name")
     * @Rest\RequestParam(name="short_name", description="Department short name")
     *
     * @ApiDoc(
     *     description="Create a new department for the given faculty",
     *     section="Departments",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when faculty cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $facultyId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(int $facultyId, ParamFetcher $paramFetcher)
    {
        /** @var DepartmentManagerService $departmentManager */
        $departmentManager = $this->get(DepartmentManagerService::SERVICE_NAME);
        /** @var FacultyManagerService $facultyManager */
        $facultyManager = $this->get(FacultyManagerService::SERVICE_NAME);

        $faculty = $facultyManager->getFacultyById($facultyId);
        $departmentFullName = $paramFetcher->get('full_name');
        $departmentShortName = $paramFetcher->get('short_name');

        $departmentManager->createNew($faculty, $departmentFullName, $departmentShortName);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/get/id/{departmentId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get department by id",
     *     section="Departments",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when department cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $departmentId
     * @param $_format
     * @return Response
     */
    public function getByIdAction(int $departmentId, $_format)
    {
        /** @var DepartmentManagerService $departmentManager */
        $departmentManager = $this->get(DepartmentManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');


        $department = $departmentManager->getDepartmentById($departmentId);
        $normalizer = new \Normalizer();


        return new Response(
            $serializer->serialize($department, $_format),
            Response::HTTP_OK);
    }
}

