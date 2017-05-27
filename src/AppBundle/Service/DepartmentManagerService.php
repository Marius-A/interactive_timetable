<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Department;
use AppBundle\Model\NodeEntity\Faculty;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Neo4j\OGM\Common\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DepartmentManagerService
 * @package AppBundle\Service
 */
class DepartmentManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.department_manager.service';

    /**
     * @param string $shortName
     * @param Faculty $faculty
     * @param string | null $fullName
     * @param Specialization[] | Collection  | null $specializations
     * @return Department
     */
    public function createNew($faculty, $fullName, $shortName, $specializations = null)
    {
        if ($fullName == null) {
            $fullName = $shortName;
        }

        $result = $this->getEntityManager()
            ->getRepository(Department::class)
            ->findOneBy(
                array(
                    'shortName' => $shortName,
                    'faculty' => $faculty
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.department.department_already_exists')
            );
        }

        $department = new Department();
        $department->setFullName($fullName)
            ->setShortName($shortName)
            ->setFaculty($faculty);

        if ($specializations != null) {
            $department->setSpecializations($specializations);
        }

        $this->getEntityManager()->persist($department);
        $this->getEntityManager()->flush();

        return $department;
    }

    /**
     * @param Specialization $specialization
     * @param Department $department
     */
    public function addSpecialization($specialization, $department)
    {
        $specialization->setDepartment($department);

        if ($department->getSpecializations()->contains($specialization)) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.department.specialization_already_exists')
            );
        }

        $department->getSpecializations()->add($specialization);

        $this->getEntityManager()->persist($department);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Department $department
     * @param Specialization[]| Collection $specializationList
     */
    public function addSpecializationList($department, $specializationList)
    {
        $iterator = $specializationList->getIterator();

        //TODO Check duplicates
        /** @var Specialization $specialization */
        while (($specialization = $iterator->next()) !== false) {
            $specialization->setDepartment($department);
            $department->getSpecializations()->add($specialization);
        }


        $this->getEntityManager()->persist($department);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Department $department
     * @param Specialization $specialization
     */
    public function removeSpecialization($department, $specialization)
    {
        if (!$department->getSpecializations()->removeElement($specialization)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.department.specialization_does_not_exists')
            );
        }

        $this->getEntityManager()->persist($department);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Department $department
     */
    public function removeDepartment($department)
    {
        $this->getEntityManager()->remove($department);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $departmentId
     * @return Department
     */
    public function getDepartmentById(int $departmentId)
    {
        $department = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Department')
            ->findOneBy(
                array(
                    'id' => $departmentId
                )
            );

        if ($department == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.department.department_does_not_exists')
            );
        }

        return $department;
    }
}