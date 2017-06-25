<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\TeachingActivity;
use AppBundle\Service\Traits\EntityManagerTrait;

class ActivityOverlapsCheckerService
{
    const SERVICE_NAME = 'app.activity_overlaps_checker.service';

    use EntityManagerTrait;

    /** @var  ParticipantManagerService */
    private $participantsManager;

    /**
     * ActivityOverlapsCheckerService constructor.
     * @param ParticipantManagerService $participantsManager
     */
    public function __construct(ParticipantManagerService $participantsManager)
    {
        $this->participantsManager = $participantsManager;
    }


    /**
     * @param TeachingActivity $teachingActivity
     * @return bool
     */
    public function checkIfTeachingActivityExists(TeachingActivity $teachingActivity){

        $participantsIds = $this->participantsManager->getParticipantsIdsByActivity($teachingActivity);

        $result = $this->getEntityManager()
            ->createQuery(
                'MATCH (a:TeachingActivity)-[:ON_SEMESTER]->(s:Semester) WHERE ID(s) = {semId}
                AND a.activityCategory = {category}
                 WITH a MATCH (su:Subject)<-[:LINKED_TO]-(a) WHERE ID(su) = {subId} WITH a
                MATCH (l:Location)-[:IN]-(a) WHERE ID(l) = {locId} WITH a
                MATCH (p:Participant)-[:PARTICIPATE]-(a) WHERE ID(p) IN ['.implode(',', $participantsIds).'] return a'
            )
        ->setParameter('semId', $teachingActivity->getSemester()->getId())
        ->setParameter('category', $teachingActivity->getActivityCategory())
        ->setParameter('subId', $teachingActivity->getSubject()->getId())
        ->setParameter('locId', $teachingActivity->getLocation()->getId())
        ->getResult();


       return count($result) > 0;
    }

}