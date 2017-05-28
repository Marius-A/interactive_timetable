<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Model\NodeEntity\SubSeries;
use AppBundle\Service\SeriesManagerService;
use AppBundle\Service\SpecializationManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SeriesRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/series",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class SeriesRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create/{specializationId}.{_format}")
     *
     * @Rest\RequestParam(name="name", description="Series  name")
     * @Rest\RequestParam(name="year_of_study", requirements="^(([01]?[0-9])|(20))$", description="Series year of study")
     *
     * @ApiDoc(
     *     description="Create a new specialization for the given department",
     *     section="Series",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when specialization cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $specializationId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postSeriesAction(int $specializationId, ParamFetcher $paramFetcher)
    {
        /** @var SeriesManagerService $seriesManager */
        $seriesManager = $this->get(SeriesManagerService::SERVICE_NAME);
        /** @var SpecializationManagerService $specializationManager */
        $specializationManager = $this->get(SpecializationManagerService::SERVICE_NAME);

        /** @var Specialization $department */
        $specialization = $specializationManager->getSpecializationById($specializationId);

        $seriesName = $paramFetcher->get('name');
        $yearOfStudy = $paramFetcher->get('year_of_study');

        $seriesManager->createNewSeries($specialization, $seriesName, $yearOfStudy);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Post("/sub_series/{seriesId}.{_format}")
     *
     * @Rest\RequestParam(name="name", requirements="[A-E]", description="SubSeries name")
     *
     * @ApiDoc(
     *     description="Add new sub series for the given series",
     *     section="Series",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when series cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $seriesId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postSubSeriesAction(int $seriesId, ParamFetcher $paramFetcher)
    {
        /** @var SeriesManagerService $seriesManager */
        $seriesManager = $this->get(SeriesManagerService::SERVICE_NAME);

        $series = $seriesManager->getSeriesById($seriesId);
        $subSeriesName = $paramFetcher->get('name');

        $subSeries = new SubSeries($subSeriesName, $series);

        $seriesManager->addSubSeries($series, $subSeries);

        return new Response('created', Response::HTTP_CREATED);
    }


    /**
     * @Rest\Delete("/sub_series/{subSeriesId}.{_format}")
     *
     *
     * @ApiDoc(
     *     description="Delete sub-series by id",
     *     section="Series",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when sub-series cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $subSeriesId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function deleteSubSeriesAction(int $subSeriesId, ParamFetcher $paramFetcher)
    {
        /** @var SeriesManagerService $seriesManager */
        $seriesManager = $this->get(SeriesManagerService::SERVICE_NAME);

        $seriesManager->removeSubSeriesById($subSeriesId);

        return new Response('deleted', Response::HTTP_OK);
    }
}