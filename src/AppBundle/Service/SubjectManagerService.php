<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Model\NodeEntity\Subject;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
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

    /** @var  Subject */
    protected $subject;

    const SERVICE_NAME = 'app.Subject_manager.service';

    /**
     * @param string $name
     * @param string $description
     * @param int $yearOfStudy
     * @param Specialization $specialization
     * @return Subject
     */
    public function createNew(string $name, string $description, int $yearOfStudy, $specialization)
    {
        $query = $this->getEntityManager()->createQuery('MATCH (s:Specialization)-[:BELONGS_TO]->(su:Subject{name:{subjectName}, yearOfStudy:{yStudy}}) WHERE ID(s) = {specId} return su;');
        $query->setParameter('specId', $specialization->getId());
        $query->setParameter('subjectName', $name);
        $query->setParameter('yStudy', $yearOfStudy);
        $query->addEntityMapping('su', Subject::class);
        $result = $query->getOneOrNullResult();

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.subject.already_exists')
            );
        }


        $subject = new Subject();
        $subject->setName($name)
            ->setDescription($description)
            ->setYearOfStudy($yearOfStudy)
            ->setSpecialization($specialization);

        $this->getEntityManager()->persist($subject);
        $this->getEntityManager()->flush();

        return $this->subject;
    }


    /**
     * @param $subjectId
     * @return Subject
     */
    public function getSubjectById($subjectId)
    {
        $subject = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Subject')
            ->findOneById($subjectId);

        if (!($subject instanceof Subject)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.subject.does_not_exists')
            );
        }

        return $subject;
    }
}