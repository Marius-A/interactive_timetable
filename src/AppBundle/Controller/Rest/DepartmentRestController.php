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
     * @QueryParam(name="full_name", requirements="^[a-zA-Z0-9_ ]*$", default=null, description="Department full name")
     * @QueryParam(name="short_name", requirements="^[a-zA-Z0-9_ ]*$", description="Department short name")
     *
     * @ApiDoc(
     *     description="Create a new department for the given faculty",
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
        $departmentShortName = $paramFetcher->get('short_ame');

        $departmentManager->createNew($faculty, $departmentFullName, $departmentShortName);

        return new Response('created', Response::HTTP_CREATED);
    }
}

