<?php


namespace AppBundle\Controller\Rest;

use AppBundle\Service\AcademicYearManagerService;
use AppBundle\Service\ParticipantManagerService;
use AppBundle\Service\TeacherManagerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ParticipantRestController
 * @package AppBundle\Controller\Rest
 *
 * @Rest\Route(
 *     "api/v1/participant",
 *     defaults={"_format": "json"},
 *     requirements={
 *         "_format": "xml|json"
 *     }
 * )
 */
class ParticipantRestController extends FOSRestController
{

    /**
     * @Rest\Get("/.{_format}")
     *
     * @ApiDoc(
     *     description="Get all participants",
     *     section="Participants",
     *     statusCodes={
     *         201="Returned when successful",
     *         500="Returned on internal server error",
     *     }
     * )
     *
     * @param string $_format
     * @return Response
     */
    public function getAllAction($_format)
    {
        /** @var ParticipantManagerService $participantManager */
        $participantManager = $this->get(ParticipantManagerService::SERVICE_NAME);
        $serializer = $this->get('serializer');

        $participants = $participantManager->getAllParticipants();

        return new Response(
            $serializer->serialize($participants, $_format),
            Response::HTTP_OK
        );
    }
}