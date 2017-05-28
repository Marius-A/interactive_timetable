<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Department;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Service\DepartmentManagerService;
use AppBundle\Service\SpecializationManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @Rest\RequestParam(name="full_name", description="Specialization full name")
     * @Rest\RequestParam(name="short_name", description="Specialization short name")
     *
     * @ApiDoc(
     *     description="Create a new specialization for the given department",
     *     section="Specializations",
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

        $specializationFullName = $paramFetcher->get('full_name');
        $specializationShortName = $paramFetcher->get('short_name');

        $specializationManager->createNew($specializationShortName, $specializationFullName, $department);

        return new Response('created', Response::HTTP_CREATED);
    }


    /**
     * @Rest\Get("/get/id/{specializationId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get specialization by id",
     *     section="Specializations",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when specialization cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $specializationId
     * @param $_format
     * @return Response
     */
    public function getByIdAction(int $specializationId, $_format)
    {
        /** @var SpecializationManagerService $departmentManager */
        $specializationManager = $this->get(SpecializationManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');


        $specialization = $specializationManager->getSpecializationById($specializationId);


        return new Response(
            $serializer->serialize($specialization, $_format),
            Response::HTTP_OK);
    }
}