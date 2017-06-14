<?php


namespace AppBundle\Service;


use AppBundle\Model\NodeEntity\Series;
use AppBundle\Model\NodeEntity\Specialization;
use AppBundle\Model\NodeEntity\SubSeries;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SeriesManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.series_manager.service';

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
     * @param int $subSeriesId
     * @return SubSeries
     */
    public function getSubSeriesSeriesById(int $subSeriesId)
    {
        /** @var SubSeries $series */
        $series = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\SubSeries')
            ->findOneById($subSeriesId);

        $this->throwNotFoundOnNullSubSeries($series);

        return $series;
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
                $this->getTranslator()->trans('app.warnings.series.does_not_exists').': '.$identifier
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
                $this->getTranslator()->trans('app.warnings.series.does_not_exists').': '.$identifier
            );
        }
    }

    /**
     * @param int $subSeriesId
     */
    public function removeSubSeriesById(int $subSeriesId)
    {
        $subSeries = $this->getSubSeriesSeriesById($subSeriesId);
        $this->getEntityManager()->remove($subSeries, true);
        $this->getEntityManager()->flush();
    }
}