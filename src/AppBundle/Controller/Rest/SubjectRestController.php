<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Service\SpecializationManagerService;
use AppBundle\Service\SubjectManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SubjectRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/subject",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 *
 */
class SubjectRestController extends FOSRestController
{
    /**
     * @Rest\Post("/create/{specializationId}.{_format}")
     *
     * @Rest\RequestParam(name="name", description="Subject name")
     * @Rest\RequestParam(name="description", description="Subject description")
     * @Rest\RequestParam(name="year_of_study", description="Year in witch this subject is studied")
     *
     * @ApiDoc(
     *     description="Create a new subject for the given specialization",
     *     section="Subjects",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when subject cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $specializationId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(int $specializationId, ParamFetcher $paramFetcher)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);
        /** @var SpecializationManagerService $specializationManager */
        $specializationManager = $this->get(SpecializationManagerService::SERVICE_NAME);


        $name = $paramFetcher->get('name');
        $description = $paramFetcher->get('description');
        $yearOfStudy = $paramFetcher->get('year_of_study');

        /** @var Specialization $specialization */
        $specialization = $specializationManager->getSpecializationById($specializationId);

        $subjectManager->createNew($name, $description, $yearOfStudy, $specialization);

        return new Response('created', Response::HTTP_CREATED);
    }


    /**
     * @Rest\Get("/get/id/{subjectId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get specialization by id",
     *     section="Subjects",
     *     statusCodes={
     *         201="Returned when successful",
     *         404="Returned when subject cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $subjectId
     * @param $_format
     * @return Response
     */
    public function getByIdAction(int $subjectId, $_format)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $specialization = $subjectManager->getSubjectById($subjectId);

        return new Response(
            $serializer->serialize($specialization, $_format),
            Response::HTTP_OK);
    }
}