<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Department;
use AppBundle\Model\NodeEntity\Series;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Model\NodeEntity\Subject;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class SpecializationManagerService
 * @package AppBundle\Service
 */
class SpecializationManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.specialization_manager.service';

    /**
     * @param string $name
     * @param Department $department
     * @param Series[] | null $series
     * @param Subject[] | null $subjects
     * @return Specialization
     */
    public function createNew($name, $department, $series = null, $subjects = null)
    {
        $result = $this->getEntityManager()
            ->getRepository(Specialization::class)
            ->findOneBy(
                array(
                    'name' => $name,
                    'department' => $department
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.specialization.specialization_already_exists')
            );
        }

        $specialization = new Specialization();
        $specialization->setName($name)
            ->setDepartment($department);

        if($series != null){
            $specialization->setSeries($series);
        }

        if ($subjects != null){
            $specialization->setSubjects($subjects);
        }

        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();

        return $specialization;
    }

    /**
     * @param Specialization $specialization
     * @param Series $series
     */
    public function addSeries(Specialization $specialization, Series $series)
    {
        $series->setSpecialization($specialization);

        if ($specialization->getSeries()->contains($series)) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.specialization.series_already_exists') . ' ' . $series->getName()
            );
        }

        $specialization->getSeries()->add($series);

        $this->getEntityManager()->persist($series);
        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Specialization $specialization
     * @param Subject $subject
     */
    public function addSubject(Specialization $specialization, Subject $subject)
    {
        $subject->setSpecialization($$specialization);

        if ($specialization->getSubjects()->contains($subject)) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.specialization.subject_already_exists') . ' ' . $subject->getName()
            );
        }

        $specialization->getSubjects()->add($subject);

        $this->getEntityManager()->persist($subject);
        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Specialization $specialization
     * @param Series $series
     */
    public function removeSeries(Specialization $specialization, Series $series)
    {
        if (!$specialization->getSeries()->removeElement($series)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.specialization.series_does_not_exists')
            );
        }

        $this->getEntityManager()->persist($series);
        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Specialization $specialization
     * @param Subject $subject
     */
    public function removeSubject(Specialization $specialization, Subject $subject)
    {
        if (!$specialization->getSubjects()->removeElement($subject)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.specialization.subject_does_not_exists')
            );
        }

        $this->getEntityManager()->remove($subject);
        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $specializationId
     */
    public function removeSpecializationById(int $specializationId)
    {
        $specialization = $this->getSpecializationById($specializationId);

        $this->getEntityManager()->remove($specialization);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $specializationId
     * @return Specialization| null
     */
    public function getSpecializationById(int $specializationId)
    {
        $specialization = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Specialization')
            ->find($specializationId);

        if ($specialization == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.specialization.specialization_does_not_exists')
            );
        }

        return $specialization;
    }
}