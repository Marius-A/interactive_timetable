<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Series;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Model\NodeEntity\SubSeries;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\OGM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeriesManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.series_manager.service';

    /** @var SpecializationManagerService */
    private $specializationManager;

    /**
     * @param Specialization $specialization
     * @param string $name
     * @param int $yearOfStudy
     */
    public function createNewSeries(Specialization $specialization, string $name, int $yearOfStudy)
    {
        $name = trim($name);

        $result = $this->getSeriesByName($specialization, $name);

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.series.already_exists')
            );
        }

        $series = new Series($name, $yearOfStudy, $specialization);

        $this->getEntityManager()->persist($series);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $identifier
     * @return Series|mixed
     */
    public function defineSeriesByIdentifier(string $identifier)
    {
        $parts = explode('.', $identifier);

        $specializationIdentifier = substr($parts[0], 0, -1);
        $year = (int)substr($parts[0], -1);

        $specialization = $this->specializationManager->getSpecializationByIdentifier($specializationIdentifier)[0];

        if ($specialization == null) {
            throw new NotFoundHttpException('Specialization wit identifier' . $specializationIdentifier . ' not found');
        }

        $result = $this->getSeriesByName($specialization, $identifier);

        if ($result != null) {
            return $result;
        }

        return new Series($identifier, $year, $specialization);
    }

    /**
     * @param string $identifier
     * @return Series|mixed
     */
    public function defineSubSeriesByIdentifier(string $identifier)
    {
        $parts = explode('.', $identifier);

        $specializationIdentifier = substr($parts[0], 0, -1);

        $parts = explode('-', $identifier);
        $subGroupName = $parts[1];


        $specialization = $this->specializationManager->getSpecializationByIdentifier($specializationIdentifier)[0];

        $result = null;
        try {
            $result = $this->getSubSeriesByIdentifier($identifier);
        }catch (\Exception $exception){}

        if ($result != null) {
            return $result;
        }

        $result = $this->getSeriesByName($specialization, $parts[0])[0];

        $this->throwNotFoundOnNullSeriesWithIdentifier($result, $parts[0]);


        return new SubSeries($subGroupName, $result);
    }

    /**
     * @param Series $series
     * @param SubSeries $subSeries
     */
    public function addSubSeries($series, $subSeries)
    {

        $query = $this->getEntityManager()->createQuery('MATCH (su:SubSeries{name:{subSerName}})-[:PART_OF]->(s:Series) WHERE ID(s) = {serId} return su;');
        $query->setParameter('serId', $series->getId());
        $query->setParameter('subSerName', $subSeries->getName());
        $query->addEntityMapping('su', SubSeries::class);
        $result = $query->getOneOrNullResult();

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.series.already_exists')
            );
        }

        $this->getEntityManager()->persist($subSeries);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $seriesId
     * @return Series
     */
    public function getSeriesById(int $seriesId)
    {
        /** @var Series $series */
        $series = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Series')
            ->findOneById($seriesId);

        $this->throwNotFoundOnNullSeries($series);

        return $series;
    }

    /**
     * @param string $seriesIdentifier
     * @return Series
     */
    public function getSeriesByIdentifier(string $seriesIdentifier)
    {
        /** @var Series $series */
        $series = $this->getEntityManager()
            ->createQuery('MATCH (s:Series) WHERE s.identifier = {identifier} RETURN s')
            ->addEntityMapping('s', Series::class)
            ->setParameter('identifier', $seriesIdentifier)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSeriesWithIdentifier($series, $seriesIdentifier);

        return $series;
    }

    /**
     * @param string $seriesIdentifier
     * @return SubSeries
     */
    public function getSubSeriesByIdentifier(string $seriesIdentifier)
    {
        /** @var SubSeries $series */
        $subSeries = $this->getEntityManager()
            ->createQuery('MATCH (s:SubSeries) WHERE s.identifier = {identifier} RETURN s')
            ->addEntityMapping('s', SubSeries::class)
            ->setParameter('identifier', $seriesIdentifier)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSubSeriesWithIdentifier($subSeries, $seriesIdentifier);

        return $subSeries;
    }

    /**
     * @param string $subSeriesId
     * @return Series
     */
    public function getSeriesBySubSeriesId(string $subSeriesId)
    {
        $series = $this->getEntityManager()
            ->createQuery('MATCH (s:SubSeries)-[:PART_OF]->(se:Series) WHERE ID(s) = ' . $subSeriesId . ' RETURN se')
            ->addEntityMapping('se', Series::class)
            ->setParameter('identifier', $subSeriesId)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSeries($series);

        return $series[0];
    }

    /**
     * @param int $subSeriesId
     * @return SubSeries
     */
    public function getSubSeriesById(int $subSeriesId)
    {
        /** @var SubSeries $series */
        $series = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\SubSeries')
            ->findOneById($subSeriesId);

        $this->throwNotFoundOnNullSubSeries($series);

        return $series;
    }


    /**
     * @param int $studentId
     * @return SubSeries
     */
    public function getSubSeriesDetailsByStudentId(int $studentId)
    {
        /** @var SubSeries $series */
        $subSeries = $this->getEntityManager()
            ->createQuery('MATCH (s:SubSeries)<-[:PART_OF]-(st:Student) WHERE ID(st) = {stId} RETURN s')
            ->addEntityMapping('s', SubSeries::class, Query::HYDRATE_RAW)
            ->setParameter('stId', $studentId)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSubSeries($subSeries);

        $subSeries = $this->getPropertiesFromSubSeriesNode($subSeries[0]['s']);

        return $subSeries;
    }

    /**
     * @param int $studentId
     * @return mixed
     */
    public function getSeriesDetailsByStudentId(int $studentId)
    {
        $series = $this->getEntityManager()
            ->createQuery('MATCH (s:Series)<-[:PART_OF*]-(st:Student) WHERE ID(st) = {stId} RETURN s')
            ->addEntityMapping('s', Series::class, Query::HYDRATE_RAW)
            ->setParameter('stId', $studentId)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSubSeries($series);

        return $series;
    }

    /**
     * @param int $subSeriesId
     * @return mixed
     */
    public function getSeriesDetailsBySubSeriesId(int $subSeriesId)
    {
        $series = $this->getEntityManager()
            ->createQuery('MATCH (s:Series)<-[:PART_OF]-(st:SubSeries) WHERE ID(st) = {stId} RETURN s')
            ->addEntityMapping('s', Series::class, Query::HYDRATE_RAW)
            ->setParameter('stId', $subSeriesId)
            ->getOneOrNullResult();

        $this->throwNotFoundOnNullSubSeries($series);

        return $this->getPropertiesFromSeriesNode($series[0]['s']);
    }

    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromSubSeriesNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['series'] = $this->getSeriesDetailsBySubSeriesId($id);

        return $values;
    }

    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromSeriesNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;

        return $values;
    }


    /**
     * @param Specialization $specialization
     * @param string $name
     * @return mixed
     */
    public function getSeriesByName($specialization, $name)
    {
        return $this->getEntityManager()
            ->createQuery('MATCH (s:Series)-[:PART_OF]->(sp) WHERE s.name = {name} AND ID(sp) = {specialization} RETURN s')
            ->addEntityMapping('s', Series::class)
            ->setParameter('name', $name)
            ->setParameter('specialization', $specialization->getId())
            ->getOneOrNullResult();
    }

    /**
     * @param Series $series
     */
    public function throwNotFoundOnNullSeries($series)
    {
        if ($series == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists')
            );
        }
    }

    /**
     * @param Series $series
     * @param $identifier
     */
    public function throwNotFoundOnNullSeriesWithIdentifier($series, $identifier)
    {
        if ($series == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists') . ': ' . $identifier
            );
        }
    }


    /**
     * @param SubSeries $subSeries
     */
    public function throwNotFoundOnNullSubSeries($subSeries)
    {
        if ($subSeries == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists')
            );
        }
    }

    /**
     * @param SubSeries $subSeries
     * @param $identifier
     */
    public function throwNotFoundOnNullSubSeriesWithIdentifier($subSeries, $identifier)
    {
        if ($subSeries == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists') . ': ' . $identifier
            );
        }
    }

    /**
     * @param int $subSeriesId
     */
    public function removeSubSeriesById(int $subSeriesId)
    {
        $subSeries = $this->getSubSeriesById($subSeriesId);
        $this->getEntityManager()->remove($subSeries, true);
        $this->getEntityManager()->flush();
    }

    /**
     * @param SpecializationManagerService $specializationManager
     * @return SeriesManagerService
     */
    public function setSpecializationManager(SpecializationManagerService $specializationManager): SeriesManagerService
    {
        $this->specializationManager = $specializationManager;
        return $this;
    }
}