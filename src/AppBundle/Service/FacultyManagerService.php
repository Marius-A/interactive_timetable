<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Department;
use AppBundle\Model\NodeEntity\Faculty;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Neo4j\OGM\Common\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class FacultyManagerService
 * @package AppBundle\Service
 */
class FacultyManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.faculty_manager.service';

    /**
     * Create new Calendar object and save it into database
     * @param string $shortName
     * @param string $fullName
     * @param Department[] | Collection | null $departments
     * @return Faculty
     */
    public function createNew($shortName, $fullName, $departments = null)
    {

        $faculty = new Faculty();
        $faculty->setFullName($fullName)
            ->setShortName($shortName);


        if ($departments != null) {
            $faculty->setDepartments($departments);
        }


        $this->getEntityManager()->persist($faculty);
        $this->getEntityManager()->flush();

        return $faculty;
    }

    /**
     * @param Department $department
     * @param Faculty $faculty
     */
    public function addDepartment($department, $faculty)
    {
        $department->setFaculty($faculty);

        if ($faculty->getDepartments()->contains($department)) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.department.already_exists')
            );
        }

        $faculty->getDepartments()->add($department);

        $this->getEntityManager()->persist($faculty);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Faculty $faculty
     * @param Department[]| Collection $departmentList
     */
    public function addDepartmentList($faculty, $departmentList)
    {
        $iterator = $departmentList->getIterator();
//todo redo
        while (($department = $iterator->next()) !== false) {
            $this->addDepartment($department, $faculty);
        }
    }

    /**
     * @param Department $department
     * @param Faculty $faculty
     */
    public function removeDepartment($department, $faculty)
    {
        if (!$faculty->getDepartments()->removeElement($department)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.department.does_not_exists')
            );
        }

        $this->getEntityManager()->remove($department);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Faculty $faculty
     */
    public function removeAllDepartments($faculty)
    {
        foreach ($faculty->getDepartments() as $department) {
            $faculty->getDepartments()->removeElement($department);
        }

        $this->getEntityManager()->persist($faculty);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Faculty $faculty
     */
    public function removeFaculty($faculty)
    {
        $this->getEntityManager()->remove($faculty);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $facultyId
     * @return Faculty
     */
    public function getFacultyById(int $facultyId)
    {
        $faculty = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Faculty')
            ->find($facultyId);
        if ($faculty == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.faculty.does_not_exists')
            );
        }

        return $faculty;
    }
}