<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Participant;
use AppBundle\Model\NodeEntity\Teacher;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ParticipantManagerService
 * @package AppBundle\Service
 */
class ParticipantManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.participant_manager.service';


    public function getParticipantsByActivityId($activityId)
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant)-[:PARTICIPATE]->(act:Activity) WHERE ID(act) = {actId} RETURN p')
            ->addEntityMapping('p', Participant::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getResult();

        $data = array();
        foreach ($participants as $participant){
            $data[] = $this->getPropertiesFromNode($participant['p']);
        }

        return $data;
    }

    private function getPropertiesFromNode(Node $node){
        $id = $node->identity();
        $values =  $node->values();
        $values['id'] = $id;
        $values['type'] = $node->labels();
        return $values;
    }
}