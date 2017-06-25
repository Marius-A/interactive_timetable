<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\AcademicYearManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AcademicYearRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/ay",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class AcademicYearRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="name", default=null, description="Academic year name   ex: 2016-2017")
     *
     * @ApiDoc(
     *     description="Create academic year with it's semesters",
     *     section="Academic year",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when an academic year with the same name already exists",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);

        $academicYearName = $paramFetcher->get('name');

        $academicYearManager->createNew($academicYearName);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/name/{name}.{_format}")
     *
     * @ApiDoc(
     *     description="Get academic year by name",
     *     section="Academic year",
     *     statusCodes={
     *         201="Returned on success",
     *         404="Returned when the academic year with the given name is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $name
     * @param string $_format
     * @return Response
     */
    public function getByNameAction($name, $_format)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYear = $academicYearManager->getAcademicYearByName($name);
        $academicYearManager->throwNotFoundExceptionIfIsAcademicYearIsNullNull($academicYear);


        return new Response(
            $serializer->serialize($academicYear, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get academic year by id",
     *     section="Academic year",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the academic year with the given id is not founded",
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
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYear = $academicYearManager->getAcademicYearById($id);
        $academicYearManager->throwNotFoundExceptionIfIsAcademicYearIsNullNull($academicYear);


        return new Response(
            $serializer->serialize($academicYear, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/.{_format}")
     *
     * @ApiDoc(
     *     description="Get all academic years",
     *     section="Academic year",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $_format
     * @return Response
     * @internal param $id
     */
    public function getAllAction($_format)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $academicYears = $academicYearManager->getAllYears();

        return new Response(
            $serializer->serialize($academicYears, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\RequestParam(name="name", default=null, description="New academic year name   ex: 2016-2017")
     *
     * @ApiDoc(
     *     description="Update academic year with the given id",
     *     section="Academic year",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the academic year with the given id is not founded",
     *         409="Returned when an academic year with the same name allready exists",
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
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);

        $academicYearName = $paramFetcher->get('name');

        $academicYearManager->updateAcademicYear($id, $academicYearName);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Remove academic year with a given id",
     *     section="Academic year",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the academic year with the given id is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @return Response
     */
    public function removeAction(int $id)
    {
        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->get(AcademicYearManagerService::SERVICE_NAME);

        $academicYearManager->removeAcademicYear($id);

        return new Response('removed', Response::HTTP_OK);
    }
}