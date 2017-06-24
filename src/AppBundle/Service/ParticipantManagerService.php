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
use \GraphAware\Neo4j\OGM\Common\Collection;

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

    /**
     * @param array $participantsIds
     * @return Collection | Participant[]
     */
    public function getParticipantsByIds(array $participantsIds)
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant) WHERE ID(p) IN ['.implode(', ', $participantsIds).'] RETURN p')
            ->addEntityMapping('p', Participant::class)
            ->getResult();

        $collection = new Collection();
        foreach ($participants as $participant){
            $collection->add($participant);
        }

        return $collection;
    }

    public function getAllParticipants()
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant) RETURN p')
            ->addEntityMapping('p', Participant::class, Query::HYDRATE_RAW)
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