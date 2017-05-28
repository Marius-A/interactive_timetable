<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\FacultyManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FacultyRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/faculty",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class FacultyRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create.{_format}")
     *
     * @Rest\RequestParam(name="full_name", description="Faculty full name")
     * @Rest\RequestParam(name="short_name", description="Faculty short name")
     *
     * @ApiDoc(
     *     description="Create a new faculty",
     *     section="Faculties",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when faculty cannot be found",
     *         400="Returned when any of the required parameters are not found",
     *         409="Returned when faculty with the same name already exists",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var FacultyManagerService $facultyManager */
        $facultyManager = $this->get(FacultyManagerService::SERVICE_NAME);

        $facultyFullName = $paramFetcher->get('full_name');
        $facultyShortName = $paramFetcher->get('short_name');

        $facultyManager->createNew($facultyShortName, $facultyFullName);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/get/id/{facultyId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get faculty by id",
     *     section="Faculties",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when faculty cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $facultyId
     * @param $_format
     * @return Response
     */
    public function getByIdAction(int $facultyId, $_format)
    {
        /** @var FacultyManagerService $facultyManager */
        $facultyManager = $this->get(FacultyManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');


        $faculty = $facultyManager->getFacultyById($facultyId);

        return new Response(
            $serializer->serialize($faculty, $_format),
            Response::HTTP_CREATED);
    }
}