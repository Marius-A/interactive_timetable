<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Subject;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class SubjectManagerService
 * @package AppBundle\Service
 */
class SubjectManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.Subject_manager.service';

    /**
     * @param string $shortName
     * @param string $fullName
     * @param string $description
     * @return Subject
     * @internal param int $yearOfStudy
     */
    public function createNew(string $shortName, string $fullName, string $description)
    {
        $subject = new Subject($shortName, $fullName, $description);

        $this->getEntityManager()->persist($subject);
        $this->getEntityManager()->flush();

        return $subject;
    }

    /**
     * @param int $subjectId
     * @param string|null $shortName
     * @param string|null $fullName
     * @param string|null $description
     */
    public function updateSubject(int $subjectId, string $shortName = '', string $fullName = '', string $description = '')
    {
        $subject = $this->getSubjectById($subjectId);

        if ($shortName != '') {
            $subject->setShortName($shortName);

            $result = $this->getSubjectByShortName($shortName);

            if ($result != null) {
                throw new HttpException(
                    Response::HTTP_CONFLICT,
                    $this->getTranslator()->trans('app.warnings.subject.already_exists')
                );
            }

        }

        if ($shortName != '') {
            $subject->setShortName($shortName);
        }

        if ($fullName != '') {
            $subject->setFullName($fullName);
        }

        if ($description != '') {
            $subject->setDescription($description);
        }


        $this->getEntityManager()->persist($subject);
        $this->getEntityManager()->flush();
    }


    /**
     * @param int $subjectId
     */
    public function removeSubjectById(int $subjectId)
    {
        $subject = $this->getSubjectById($subjectId);

        $this->getEntityManager()->remove($subject);
        $this->getEntityManager()->flush();

    }


    /**
     * @param string $name
     * @return Subject | null
     */
    public function getSubjectByShortName(string $name)
    {
        return $this->getEntityManager()
            ->createQuery('MATCH (s:Subject) WHERE s.shortName = {name} RETURN s')
            ->addEntityMapping('s', Subject::class)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return Subject|null
     */
    public function getSubjectById(int $id)
    {
        /** @var Subject $subject */
        $subject = $this->getEntityManager()
            ->getRepository(Subject::class)
            ->findOneById($id);

        $this->throwNotFoundExceptionOnNullSubject($subject);

        return $subject;
    }

    /**
     * @param string $partialName
     * @return Subject[]
     */
    public function getSubjectsWithShortNameNameLike(string $partialName)
    {
        $partialName = '.*' . strtolower($partialName) . '.*';
        return $this->getEntityManager()
            ->createQuery("MATCH (s:Subject) WHERE toLower(s.shortName) =~ {name} RETURN s")
            ->addEntityMapping('l', Subject::class)
            ->setParameter('name', $partialName)
            ->getResult();
    }

    /**
     * @return array
     */
    public function getAllSubjectsDetails()
    {
        $result =  $this->getEntityManager()
            ->createQuery("MATCH (s:Subject) RETURN s")
            ->addEntityMapping('l', Subject::class, Query::HYDRATE_RAW)
            ->getResult();

        $subjects = array();
        foreach ($result as $node){
            $subjects[] = $this->getPropertiesFromSubjectNode($node['s']);
        }

        return $subjects;
    }

    /**
     * @param string $partialName
     * @return Subject[]
     */
    public function getSubjectsWithFullNameNameLike(string $partialName)
    {
        $partialName = '.*' . strtolower($partialName) . '.*';
        return $this->getEntityManager()
            ->createQuery("MATCH (s:Subject) WHERE toLower(s.shortName) =~ {name} RETURN s")
            ->addEntityMapping('s', Subject::class)
            ->setParameter('name', $partialName)
            ->getResult();
    }

    /**
     * @param Subject $subject
     */
    public function throwNotFoundExceptionOnNullSubject($subject)
    {
        if ($subject == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.subject.does_not_exists')
            );
        }
    }

    /**
     * @param Subject $subject
     */
    public function throwNotFoundExceptionOnNullSubjectWithName($subject, $name)
    {
        if ($subject == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.subject.does_not_exists').' '. $name
            );
        }
    }

    public function getSubjectByActivityId($activityId)
    {
        $subject = $this->getEntityManager()
            ->createQuery('MATCH (s:Subject)<-[:LINKED_TO]-(act:Activity) WHERE ID(act) = {actId} RETURN s')
            ->addEntityMapping('s', Subject::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullSubject($subject);

        return $this->getPropertiesFromSubjectNode($subject[0]['s']);
    }

    /**
     * @param \GraphAware\Common\Type\Node $node
     * @return array
     */
    private function getPropertiesFromSubjectNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;

        return $values;
    }

}