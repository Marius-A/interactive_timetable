<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Teacher;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class TeacherManagerService
 * @package AppBundle\Service
 */
class TeacherManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.teacher_manager.service';

    /**
     * @param string $name
     * @param string $surname
     * @param string $emailAddress
     * @return void
     *
     */
    public function createNew(string $name, string $surname, string $emailAddress)
    {
        $teacher = new Teacher($name, $surname, $emailAddress);

        $this->getEntityManager()->persist($teacher);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $teacherId
     * @param string|null $name
     * @param string|null $surname
     * @param string|null $emailAddress
     */
    public function updateTeacher(int $teacherId, string $name = '', string $surname = '', string $emailAddress = '')
    {
        /** @var Teacher $teacher */
        $teacher = $this->getTeacherById($teacherId);

        if ($name != '') {
            $teacher->setName($name);
        }

        if ($surname != '') {
            $teacher->setSurname($surname);
        }

        if ($emailAddress != '') {
            $teacher->setEmail($emailAddress);
        }

        $this->getEntityManager()->persist($teacher);
        $this->getEntityManager()->flush();
    }


    /**
     * @param int $teacherId
     */
    public function removeTeacherById(int $teacherId)
    {
        $teacher = $this->getTeacherById($teacherId);

        $this->getEntityManager()->remove($teacher);
        $this->getEntityManager()->flush();

    }

    /**
     * @param int $id
     * @return Teacher|null
     */
    public function getTeacherById(int $id)
    {
        /** @var Teacher $teacher */
        $teacher = $this->getEntityManager()
            ->getRepository(Teacher::class)
            ->findOneById($id);

        $this->throwNotFoundExceptionOnNullTeacher($teacher);

        return $teacher;
    }

    /**
     * @return array
     */
    public function getAllTeachers()
    {
        $result =  $this->getEntityManager()
            ->createQuery("MATCH (t:Teacher) RETURN t")
            ->addEntityMapping('t', Teacher::class, Query::HYDRATE_RAW)
            ->getResult();

        $teachers = array();
        foreach ($result as $teacher){
            $teachers[] = $this->getPropertiesFromTeacherNode($teacher['t']);
        }

        return $teachers;
    }

    /**
     * @param string $partialName
     * @return Teacher[]
     */
    public function getTeachersWithFullNameLike(string $partialName)
    {
        $partialName = '.*' . strtolower($partialName) . '.*';
        return $this->getEntityManager()
            ->createQuery("MATCH (t:Teacher) WHERE toLower(t.name) =~ {name} OR toLower(t.surname) =~ {name}  RETURN t")
            ->addEntityMapping('t', Teacher::class)
            ->setParameter('name', $partialName)
            ->getResult();
    }

    /**
     * @param string $fullName
     * @return Teacher
     */
    public function getTeacherByFullName(string $fullName)
    {
        $names = explode(' ', $fullName);

        if (count($names) < 2) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Teacher must have at least one name and one surname');
        }

        $name1 = '.*' . strtolower($names[0]) . '.*';
        $name2 = '.*' . strtolower($names[1]) . '.*';

        return $this->getEntityManager()
            ->createQuery("MATCH (t:Teacher) WHERE (toLower(t.name) =~ {name1} AND toLower(t.surname) =~ {name2}) OR (toLower(t.name) =~ {name2} AND toLower(t.surname) =~ {name1})   RETURN t")
            ->addEntityMapping('t', Teacher::class)
            ->setParameter('name1', $name1)
            ->setParameter('name2', $name2)
            ->getOneOrNullResult();
    }

    /**
     * @param Teacher $teacher
     */
    public function throwNotFoundExceptionOnNullTeacher($teacher)
    {
        if ($teacher == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.teacher.does_not_exists')
            );
        }
    }

    public function getTeacherByTeachingActivityId($activityId)
    {
        $teacher = $this->getEntityManager()
            ->createQuery('MATCH (t:Teacher)-[:TEACHED_BY]->(act:TeachingActivity) WHERE ID(act) = {actId} RETURN t')
            ->addEntityMapping('t', Teacher::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullTeacher($teacher);

        return $this->getPropertiesFromTeacherNode($teacher[0]['t']);
    }

    public function getTeacherByEvaluationActivityId($activityId)
    {
        $teacher = $this->getEntityManager()
            ->createQuery('MATCH (t:Teacher)<-[:SUPERVISED_BY]-(act:EvaluationActivity) WHERE ID(act) = {actId} RETURN t')
            ->addEntityMapping('t', Teacher::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullTeacher($teacher);

        return $this->getPropertiesFromTeacherNode($teacher[0]['t']);
    }

    /**
     * @param $email
     * @return array
     * @internal param int $id
     */
    public function getTeacherDetailsByEmail($email)
    {
        $teacher = $this->getEntityManager()
            ->createQuery("MATCH (s:Teacher) WHERE s.email = {email} RETURN s")
            ->addEntityMapping('s', Teacher::class, Query::HYDRATE_RAW)
            ->setParameter('email', $email)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNullTeacher($teacher);

        return $this->getPropertiesFromTeacherNode($teacher[0]['s']);
    }


    /**
     * @param \GraphAware\Common\Type\Node $node
     * @return array
     */
    private function getPropertiesFromTeacherNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;

        return $values;
    }

    /**
     * @param Teacher $teacher
     */
    public function throwNotFoundExceptionOnNullTeacherWithName($teacher, $name)
    {
        if ($teacher == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.teacher.does_not_exists'). ': '. $name
            );
        }
    }


}