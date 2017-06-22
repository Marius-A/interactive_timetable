<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Student;
use AppBundle\Model\NodeEntity\SubSeries;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class StudentManagerService
 * @package AppBundle\Service
 */
class StudentManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.student_manager.service';


    /** @var  SeriesManagerService */
    private $seriesManagerService;

    /**
     * @param string $name
     * @param string $surname
     * @param string $emailAddress
     * @return void
     */
    public function createNew(string $name, string $surname, string $emailAddress, int $subgroupId)
    {
        /** @var SubSeries $subseries */
        $subseries = $this->seriesManagerService->getSubSeriesById($subgroupId);
        
        $student = new Student($name, $surname, $emailAddress, $subseries);

        $this->getEntityManager()->persist($student);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $studentId
     * @param string|null $name
     * @param string|null $surname
     * @param string|null $emailAddress
     * @param int $subgroupId
     */
    public function updateStudent(int $studentId, string $name = '', string $surname = '', string $emailAddress = '', int $subgroupId = 0)
    {
        /** @var Student $student */
        $student = $this->getStudentById($studentId);

        if ($name != '') {
            $student->setName($name);
        }

        if ($surname != '') {
            $student->setSurname($surname);
        }

        if ($emailAddress != '') {
            $student->setEmail($emailAddress);
        }

        if ($subgroupId != -1) {
            /** @var SubSeries $subgroup */
            $subgroup = $this->seriesManagerService->getSubSeriesById($subgroupId);
            $student->setSubSeries($subgroup);
        }

        $this->getEntityManager()->persist($student);
        $this->getEntityManager()->flush();
    }


    /**
     * @param int $studentId
     */
    public function removeStudentById(int $studentId)
    {
        $student = $this->getStudentById($studentId);

        $this->getEntityManager()->remove($student);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $id
     * @return Student
     */
    public function getStudentById(int $id)
    {
        /** @var Student $student */
        $student = $this->getEntityManager()
            ->createQuery("MATCH (t:Student) WHERE ID(t) = {stId} RETURN t")
            ->addEntityMapping('t', Student::class)
            ->setParameter('stId', $id)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullStudent($student);

        return $student[0];
    }


    /**
     * @param int $id
     * @return array
     */
    public function getStudentDetailsById($id)
    {
        $student = $this->getEntityManager()
            ->createQuery("MATCH (s:Student) WHERE ID(s) = ".$id." RETURN s")
            ->addEntityMapping('s', Student::class, Query::HYDRATE_RAW)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullStudent($student);

        return $this->getPropertiesFromNode($student[0]['s']);
    }

    /**
     * @param $email
     * @return array
     * @internal param int $id
     */
    public function getStudentDetailsByEmail($email)
    {
        $student = $this->getEntityManager()
            ->createQuery("MATCH (s:Student) WHERE s.email = {email} RETURN s")
            ->addEntityMapping('s', Student::class, Query::HYDRATE_RAW)
            ->setParameter('email', $email)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullStudent($student);

        return $this->getPropertiesFromNode($student[0]['s']);
    }


    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromNode($node){
        $id = $node->identity();
        $values =  $node->values();
        $values['id'] = $id;
        $values['subSeries'] = $this->seriesManagerService->getSubSeriesDetailsByStudentId($id);

        return $values;
    }

    /**
     * @param string $partialName
     * @return Student[]
     */
    public function getStudentWithFullNameLike(string $partialName)
    {
        $partialName = '.*' . strtolower($partialName) . '.*';
        return $this->getEntityManager()
            ->createQuery("MATCH (t:Student) WHERE toLower(t.name) =~ {name} OR toLower(t.surname) =~ {name}  RETURN t")
            ->addEntityMapping('s', Student::class)
            ->setParameter('name', $partialName)
            ->getResult();
    }

    /**
     * @param string $email
     * @return Student
     */
    public function getStudentByEmailAddress(string $email)
    {
        $email = strtolower($email);
        $student = $this->getEntityManager()
            ->createQuery("MATCH (s:Student) WHERE toLower(s.email) = {email} RETURN s")
            ->addEntityMapping('s', Student::class)
            ->setParameter('email', $email)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullStudent($student);

        return $student[0];
    }

    /**
     * @param string $fullName
     * @return Student
     */
    public function getStudentByFullName(string $fullName)
    {
        $names = explode(' ', $fullName);

        if (count($names) < 2) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Student must have at least one name and one surname');
        }

        $name1 = '.*' . strtolower($names[0]) . '.*';
        $name2 = '.*' . strtolower($names[1]) . '.*';

        return $this->getEntityManager()
            ->createQuery("MATCH (t:Student) WHERE (toLower(t.name) =~ {name1} AND toLower(t.surname) =~ {name2}) OR (toLower(t.name) =~ {name2} AND toLower(t.surname) =~ {name1})   RETURN t")
            ->addEntityMapping('t', Student::class)
            ->setParameter('name1', $name1)
            ->setParameter('name2', $name2)
            ->getOneOrNullResult();
    }

    /**
     * @param Student $student
     */
    public function throwNotFoundExceptionOnNullStudent($student)
    {
        if ($student == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.student.does_not_exists')
            );
        }
    }

    /**
     * @param Student $student
     */
    public function throwNotFoundExceptionOnNullStudentWithIdentifier($student, $identifier)
    {
        if ($student == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.student.does_not_exists'). ': '. $identifier
            );
        }
    }

    /**
     * @return SeriesManagerService
     */
    public function getSeriesManagerService(): SeriesManagerService
    {
        return $this->seriesManagerService;
    }

    /**
     * @param SeriesManagerService $seriesManagerService
     */
    public function setSeriesManagerService(SeriesManagerService $seriesManagerService)
    {
        $this->seriesManagerService = $seriesManagerService;
    }
}