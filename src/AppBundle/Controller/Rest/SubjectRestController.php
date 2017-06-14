<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Service\SpecializationManagerService;
use AppBundle\Service\SubjectManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use GraphAware\Neo4j\OGM\Common\Collection;
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
     * @Rest\Post("/.{_format}")
     *
     * @Rest\RequestParam(name="shortName", description="Subject short-name ex: D.A.D.R")
     * @Rest\RequestParam(name="fullName", description="Subject full-name ex: Dezvoltarea aplicatiilor distribuite in retele")
     * @Rest\RequestParam(name="description", description="Subject description")
     *
     * @ApiDoc(
     *     description="Create a new subject",
     *     section="Subjects",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when subject with the same short name was found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);

        $shortName = $paramFetcher->get('shortName');
        $fullName = $paramFetcher->get('fullName');
        $description = $paramFetcher->get('description');

       $subjectManager->createNew($shortName,$fullName, $description);

        return new Response('created', Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put("{subjectId}/.{_format}")
     *
     * @Rest\QueryParam(name="shortName", description="Subject short-name ex: D.A.D.R")
     * @Rest\QueryParam(name="fullName", description="Subject full-name ex: Dezvoltarea aplicatiilor distribuite in retele")
     * @Rest\QueryParam(name="description", description="Subject description")
     *
     * @ApiDoc(
     *     description="Update subject",
     *     section="Subjects",
     *     statusCodes={
     *         201="Returned when successful",
     *         409="Returned when subject with the same short name was found",
     *         404="Returned when subject was not founded",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $subjectId
     * @param ParamFetcher $paramFetcher
     * @return Response
     */
    public function updateAction(int $subjectId, ParamFetcher $paramFetcher)
    {

        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);

        $shortName = $paramFetcher->get('shortName');
        $fullName = $paramFetcher->get('fullName');
        $description = $paramFetcher->get('description');


        $subjectManager->updateSubject($subjectId, $shortName,$fullName, $description);

        return new Response('updated', Response::HTTP_CREATED);
    }


    /**
     * @Rest\Get("/{subjectId}.{_format}")
     *
     * @ApiDoc(
     *     description="Get subject by id",
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

        $subject = $subjectManager->getSubjectById($subjectId);

        $subject->setEvaluationActivities(new Collection());
        $subject->setTeachingActivities(new Collection());

        return new Response(
            $serializer->serialize($subject, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Delete("/{subjectId}.{_format}")
     *
     * @ApiDoc(
     *     description="Remove subject by id",
     *     section="Subjects",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Returned when subject cannot be found",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param int $subjectId
     * @return Response
     */
    public function removeAction(int $subjectId)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);

        $subjectManager->removeSubjectById($subjectId);

        return new Response(
            'removed', Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("partial-short-name/{shortName}.{_format}")
     *
     * @ApiDoc(
     *     description="Get subjects with full-name like : ....",
     *     section="Subjects",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $shortName
     * @param $_format
     * @return Response
     */
    public function getSubjectsWithShortNameLikeAction(string $shortName, $_format)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $subjects = $subjectManager->getSubjectsWithShortNameNameLike($shortName);

        return new Response(
            $serializer->serialize($subjects, $_format),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Get("partial-full-name/{fullName}.{_format}")
     *
     * @ApiDoc(
     *     description="Get subjects with full-name like : ....",
     *     section="Subjects",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $fullName
     * @param $_format
     * @return Response
     */
    public function getSubjectsWithFullNameLikeAction(string $fullName, $_format)
    {
        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->get(SubjectManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $subjects = $subjectManager->getSubjectsWithFullNameNameLike($fullName);

        return new Response(
            $serializer->serialize($subjects, $_format),
            Response::HTTP_OK
        );
    }
}