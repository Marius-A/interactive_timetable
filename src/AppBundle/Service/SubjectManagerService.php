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
     * @param Specialization $specialization
     * @return Subject
     */
    public function createNew($name, $description, $specialization)
    {
        $result = $this->getEntityManager()
            ->getRepository(Subject::class)
            ->findOneBy(
                array(
                    'name' => $name
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.subject.subject_already_exists')
            );
        }

        $subject = new Subject();
        $subject->setName($name)
            ->setDescription($description)
            ->setSpecialization($specialization);

        $this->subject = $subject;

        $this->getEntityManager()->persist($this->subject);
        $this->getEntityManager()->flush();

        return $this->subject;
    }

    /**
     *
     */
    public function removeSubject()
    {
        $this->getEntityManager()->remove($this->subject);
        $this->getEntityManager()->flush();
    }
}