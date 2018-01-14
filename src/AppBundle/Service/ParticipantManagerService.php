<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\EvaluationActivity;
use AppBundle\Model\NodeEntity\Participant;
use AppBundle\Model\NodeEntity\TeachingActivity;
use AppBundle\Model\NodeEntity\Util\ParticipantType;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\OGM\Common\Collection;
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

    /** @var  SeriesManagerService */
    private $seriesManager;

    /**
     * ParticipantManagerService constructor.
     * @param SeriesManagerService $seriesManager
     */
    public function __construct(SeriesManagerService $seriesManager)
    {
        $this->seriesManager = $seriesManager;
    }


    /**
     * @param $activityId
     * @return array
     */
    public function getParticipantsByActivityId($activityId)
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant)-[:PARTICIPATE]->(act:Activity) WHERE ID(act) = {actId} RETURN p')
            ->addEntityMapping('p', Participant::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getResult();

        $data = array();
        foreach ($participants as $participant) {
            $data[] = $this->getPropertiesFromParticipantNode($participant['p']);
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
            ->createQuery('MATCH (p:Participant) WHERE ID(p) IN [' . implode(', ', $participantsIds) . '] RETURN p')
            ->addEntityMapping('p', Participant::class)
            ->getResult();

        $collection = new Collection();
        foreach ($participants as $participant) {
            $collection->add($participant);
        }

        return $collection;
    }

    /**
     * @param int $participantsId
     * @return Participant
     */
    public function getParticipantById(int $participantsId)
    {
        $participant = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant) WHERE ID(p) {pId} RETURN p')
            ->addEntityMapping('p', Participant::class)
            ->setParameter('pId', $participantsId)
            ->getOneOrNullResult();

        if(is_null($participant)){
            throw new HttpException(Response::HTTP_NOT_FOUND, $participantsId);
        }

        return $participant[0];
    }

    /**
     * @return array
     */
    public function getAllParticipants()
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH (p:Participant) RETURN p')
            ->addEntityMapping('p', Participant::class, Query::HYDRATE_RAW)
            ->getResult();

        $data = array();
        foreach ($participants as $participant) {
            $data[] = $this->getPropertiesFromParticipantNode($participant['p']);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getAllDepartments()
    {
        $participants = $this->getEntityManager()
            ->createQuery('MATCH p=(n:Department)<-[:PART_OF*..]-(m) WITH COLLECT(p) AS ps CALL apoc.convert.toTree(ps) yield value RETURN value;')
            ->getResult();

        return $participants;
    }

    /**
     * @param TeachingActivity | EvaluationActivity $activity
     * @return array
     */
    public function getParticipantsIdsByActivity($activity)
    {
        $participantsIds = array();
        foreach ($activity->getParticipants() as $participant) {
            $participantsIds[] = $participant->getId();
        }

        return $participantsIds;
    }

    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromParticipantNode(Node $node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['type'] = $node->labels();
        return $values;
    }

    /**
     * @param string $serializedParticipants
     * @return Collection
     */
    public function deserializeParticipants(string $serializedParticipants)
    {
        $splited = explode('|', $serializedParticipants);

        $participants = new Collection();

        foreach ($splited as $serializedParticipant) {
            $x = explode(':', $serializedParticipant);

            $type = $this->getParticipantTypeFromRo(trim($x[0]));
            $identifier = trim($x[1]);
            $participant = $this->getParticipantByTypeAndIdentifier($type, $identifier);
            $participants->add($participant);
        }

        return $participants;
    }

    /**
     * @param $type
     * @param $identifier
     * @return Participant
     */
    private function getParticipantByTypeAndIdentifier($type, $identifier)
    {
        if (!ParticipantType::isValidValue(strtolower($type))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid participant type:' . $type);
        }
        //TODO add specialization

        $participant = null;

        switch ($type) {
            case ParticipantType::SERIES:
                $participant = $this->seriesManager->getSeriesByIdentifier($identifier)[0];
                break;
            case ParticipantType::SUB_SERIES:
                $participant = $this->seriesManager->getSubSeriesByIdentifier($identifier)[0];
                break;
        }

        return $participant;
    }

    /**
     * @param $type
     * @return null|string
     */
    public function getParticipantTypeFromRo($type)
    {
        switch ($type) {
            case 'grupa':
                return ParticipantType::SERIES;
            case 'subgrupa':
                return ParticipantType::SUB_SERIES;
            case 'specializare':
                return ParticipantType::SPECIALIZATION;
            default:
                return null;
        }
    }
}