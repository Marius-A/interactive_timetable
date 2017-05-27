<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Department;
use AppBundle\Service\DepartmentManagerService;
use AppBundle\Service\SpecializationManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SpecializationRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/specialization",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class SpecializationRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create/{departmentId}.{_format}")
     *
     * @QueryParam(name="name", requirements="^[a-zA-Z0-9_ ]*$", description="Specialization name")
     *
     * @ApiDoc(
     *     description="Create a new specialization for the given department",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when department cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $departmentId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(int $departmentId, ParamFetcher $paramFetcher)
    {
        /** @var DepartmentManagerService $departmentManager */
        $departmentManager = $this->get(DepartmentManagerService::SERVICE_NAME);
        /** @var SpecializationManagerService $specializationManager */
        $specializationManager = $this->get(SpecializationManagerService::SERVICE_NAME);

        /** @var Department $department */
        $department = $departmentManager->getDepartmentById($departmentId);

        $specializationName = $paramFetcher->get('name');

        $specializationManager->createNew($specializationName, $department);

        return new Response('created', Response::HTTP_CREATED);
    }
}