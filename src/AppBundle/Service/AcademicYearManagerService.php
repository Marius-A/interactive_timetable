<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\AcademicYear;
use AppBundle\Model\NodeEntity\Semester;
use AppBundle\Service\Traits\EntityManagerTrait;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AcademicYearManagerService
 * @package AppBundle\Service
 */
class AcademicYearManagerService
{
    use EntityManagerTrait;

    const SERVICE_NAME = 'app.academic_year_manager.service';

    /**
     * @param string $name
     * @return AcademicYear
     */
    public function createNew(string $name)
    {
        $academicYear = new AcademicYear($name);

        $result = $this->getAcademicYearByName($name);

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                'allready exists'
            );
        }

        $this->getEntityManager()->persist($academicYear);
        $this->getEntityManager()->flush();

        return $academicYear;
    }

    /**
     * @param int $id
     * @param string $name
     * @return AcademicYear
     */
    public function updateAcademicYear(int $id, string $name)
    {
        $academicYear = $this->getAcademicYearById($id);

        if ($academicYear == null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                'not found'
            );
        }

        $academicYear->setName($name);

        $this->getEntityManager()->persist($academicYear);
        $this->getEntityManager()->flush();

        return $academicYear;
    }


    /**
     * @param int $id
     */
    public function removeAcademicYear(int $id)
    {

        $academicYear = $this->getAcademicYearById($id);
        $this->throwNotFoundExceptionIfIsAcademicYearIsNullNull($academicYear);

        $this->getEntityManager()->remove($academicYear);
    }

    /**
     * @param string $name
     * @return AcademicYear | null
     */
    public function getAcademicYearByName(string $name)
    {
        /** @var AcademicYear $academicYear */
        $academicYear = $this->getEntityManager()
            ->getRepository(AcademicYear::class)
            ->findOneBy(
                array('name' => $name)
            );

        return $academicYear;
    }

    /**
     * @param int $id
     * @return AcademicYear | null
     */
    public function getAcademicYearById(int $id)
    {
        /** @var AcademicYear $academicYear */
        $academicYear = $this->getEntityManager()
            ->getRepository(AcademicYear::class)
            ->findOneById($id);

        return $academicYear;
    }

    /**
     * @return array
     */
    public function getAllYears()
    {
        $result = $this->getEntityManager()
            ->createQuery('MATCH (ac:AcademicYear) RETURN ac')
            ->addEntityMapping('ac', AcademicYear::class, Query::HYDRATE_RAW)
            ->getResult();

        $years = array();
        foreach ($result as $item){
            $years[] = $item['ac']->values();
        }

        return $years;
    }

    /**
     * @param AcademicYear $academicYear
     * @return Semester[]|Collection
     */
    public function getSemesterByAcademicYear(AcademicYear $academicYear)
    {
        $semesters = $this->getEntityManager()
            ->getRepository(Semester::class)
            ->findBy(
                array('academicYear' => $academicYear->getId())
            );

        return $semesters;
    }

    /**
     * @param $semesterId
     * @return Semester
     */
    public function getSemesterById($semesterId)
    {
        /** @var Semester $semester */
        $semester = $this->getEntityManager()
            ->getRepository(Semester::class)
            ->findOneById($semesterId);

        $this->throwNotFoundExceptionIfSemesterIsNull($semester);

        return $semester;
    }

    /**
     * @param AcademicYear $academicYear
     * @param $number
     * @return Semester
     */
    public function getSemesterByAcademicYearAndNumber(AcademicYear $academicYear, int $number)
    {

        /** @var Semester $result */
        $semester = $this->getEntityManager()
            ->createQuery('MATCH (s:Semester)-[:HAVE]->(ay) WHERE s.number = {number} AND ID(ay) = {academicYear} RETURN s')
            ->addEntityMapping('s', Semester::class)
            ->setParameter('academicYear', $academicYear->getId())
            ->setParameter('number', $number)
            ->getOneOrNullResult();


        $this->throwNotFoundExceptionIfSemesterIsNull($semester);

        return $semester[0];
    }

    /**
     * @param $academicYearName
     * @param int $number
     * @return Semester
     */
    public function getSemesterByAcademicYearNameAndNumber($academicYearName, int $number)
    {

        /** @var Semester $result */
        $semester = $this->getEntityManager()
            ->createQuery('MATCH (s:Semester)-[:HAVE]->(ay) WHERE s.number = {number} AND ay.name = {academicYear} RETURN s')
            ->addEntityMapping('s', Semester::class)
            ->setParameter('academicYear', $academicYearName)
            ->setParameter('number', $number)
            ->getOneOrNullResult();


        $this->throwNotFoundExceptionIfSemesterIsNull($semester);

        return $semester[0];
    }

    /**
     * @param AcademicYear $academicYear
     */
    public function throwNotFoundExceptionIfIsAcademicYearIsNullNull($academicYear)
    {
        if ($academicYear == null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                'not found'
            );
        }
    }


    public function getSemesterByActivityId($activityId)
    {

        $semester = $this->getEntityManager()
            ->createQuery('MATCH (s:Semester)<-[:ON_SEMESTER]-(act:Activity) WHERE ID(act) = {actId} RETURN s')
            ->addEntityMapping('s', Semester::class, Query::HYDRATE_RAW)
            ->setParameter('actId', $activityId)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionIfSemesterIsNull($semester);

        return $this->getPropertiesFromSemesterNode($semester[0]['s']);
    }


    public function getAcademicYearBySemesterId($semesterId)
    {
        $ac = $this->getEntityManager()
            ->createQuery('MATCH (ac:AcademicYear)<-[:HAVE]-(sem:Semester) WHERE ID(sem) = {semId} RETURN ac')
            ->addEntityMapping('ac', AcademicYear::class, Query::HYDRATE_RAW)
            ->setParameter('semId', $semesterId)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionIfIsAcademicYearIsNullNull($ac);

        return $ac[0]['ac']->values();
    }

    /**
     * @param \GraphAware\Common\Type\Node $node
     * @return array
     */
    private function getPropertiesFromSemesterNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['academicYear'] = $this->getAcademicYearBySemesterId($id);

        return $values;
    }

    /**
     * @param Semester $semester
     */
    public function throwNotFoundExceptionIfSemesterIsNull($semester)
    {
        if ($semester == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                'semster not found'
            );
        }
    }
}