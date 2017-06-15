<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Department;
use AppBundle\Model\NodeEntity\Series;
use AppBundle\Model\NodeEntity\Specialization;
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
     * @param string $shortName
     * @param string $fullName
     * @param string $specializationCategory
     * @param Department $department
     * @param Series[] | null $series
     * @return Specialization
     */
    public function createNew(string $shortName, string $fullName, string $specializationCategory, Department $department, $series = null)
    {
        $result = $this->getSpecializationByNameDepartmentAndCategory($shortName, $department->getId(), $specializationCategory);

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.specialization.already_exists')
            );
        }

        $specialization = new Specialization($shortName, $fullName, $specializationCategory, $department);


        if ($series != null) {
            $specialization->setSeries($series);
        }


        $this->getEntityManager()->persist($specialization);
        $this->getEntityManager()->flush();

        return $specialization;
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
        /** @var Specialization $specialization */
        $specialization = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Specialization')
            ->findOneById($specializationId);

        if ($specialization == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.specialization.does_not_exists')
            );
        }

        return $specialization;
    }

    /**
     * @param string $name
     * @param string $departmentId
     * @param string $category
     * @return Specialization
     */
    public function getSpecializationByNameDepartmentAndCategory(string $name,string $departmentId,string $category){
        return $this->getEntityManager()
            ->createQuery('MATCH (s:Specialization)-[:PART_OF]->(d:Department) WHERE s.shortName = {name} AND s.specializationCategory = {category} AND ID(d) = {departmentId} RETURN s')
            ->addEntityMapping('s', Specialization::class)
            ->setParameter('name', $name)
            ->setParameter('category', $category)
            ->setParameter('departmentId', $departmentId)
            ->getOneOrNullResult();
    }
}