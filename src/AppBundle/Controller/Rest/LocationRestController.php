<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\LocationManagerService;
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
 *     "api/v1/location",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class LocationRestController extends FOSRestController
{
    /**
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="name", default=null, description="Location name name   ex: C4")
     *
     * @ApiDoc(
     *     description="Create a location",
     *     section="Location",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when an location with same name is founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var LocationManagerService $locationManager */
        $locationManager = $this->get(LocationManagerService::SERVICE_NAME);

        $locationName = $paramFetcher->get('name');

        $locationManager->createNew($locationName);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get("/name/{name}.{_format}")
     *
     * @ApiDoc(
     *     description="Get location by name",
     *     section="Location",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the location year with the given name is not founded",
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
        /** @var LocationManagerService $locationManager */
        $locationManager = $this->get(LocationManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $location = $locationManager->getLocationByName($name);
        $locationManager->throwNotFoundExceptionOnNullLocation($location);

        return new Response(
            $serializer->serialize($location[0], $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/partial_name/{name}.{_format}")
     *
     * @ApiDoc(
     *     description="Get locations with name like ...",
     *     section="Location",
     *     statusCodes={
     *         201="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $name
     * @param string $_format
     * @return Response
     */
    public function getWithNameLikeAction($name, $_format)
    {
        /** @var LocationManagerService $locationManager */
        $locationManager = $this->get(LocationManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $locations = $locationManager->getLocationsWithNameLike($name);

        return new Response(
            $serializer->serialize($locations, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Get location by id",
     *     section="Location",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when the location with the given id is not founded",
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
        /** @var LocationManagerService $locationManager */
        $locationManager = $this->get(LocationManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $location = $locationManager->getLocationById($id);
        $locationManager->throwNotFoundExceptionOnNullLocation($location);


        return new Response(
            $serializer->serialize($location, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put("/{id}.{_format}")
     *
     * @Rest\RequestParam(name="name", default=null, description="New location name   ex: C4")
     *
     * @ApiDoc(
     *     description="Update location with the given id",
     *     section="Location",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the location with the given id is not founded",
     *         409="Returned when a location with the same name already exists",
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
        /** @var LocationManagerService $locationManagerService */
        $locationManagerService = $this->get(LocationManagerService::SERVICE_NAME);

        $newName = $paramFetcher->get('name');

        $locationManagerService->updateLocationName($id, $newName);

        return new Response('updated', Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/{id}.{_format}")
     *
     * @ApiDoc(
     *     description="Remove location with a given id",
     *     section="Location",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when the location with the given id is not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $id
     * @return Response
     */
    public function removeAction(int $id)
    {
        /** @var LocationManagerService $locationManagerService */
        $locationManagerService = $this->get(LocationManagerService::SERVICE_NAME);

        $locationManagerService->removeLocationById($id);

        return new Response('removed', Response::HTTP_OK);
    }
}