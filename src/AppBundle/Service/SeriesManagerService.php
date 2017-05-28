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
     * @param SubSeries[] | null $subSeries
     * @return Series
     */
    public function createNewSeries(Specialization $specialization, string $name, int $yearOfStudy, $subSeries = null)
    {
        $result = $this->getEntityManager()
            ->getRepository(Series::class)
            ->findBy(
                array(
                    'name' => $name
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.series.already_exists')
            );
        }

        $series = new Series();
        $series->setName($name)
            ->setYearOfStudy($yearOfStudy)
            ->setSpecialization($specialization);

        if ($subSeries != null) {
            $series->setSubSeries($subSeries);
        }

        $this->getEntityManager()->persist($series);
        $this->getEntityManager()->flush();

        return $series;
    }

    /**
     * @param Series $series
     * @param SubSeries $subSeries
     */
    public function addSubSeries($series, $subSeries)
    {

        $query = $this->getEntityManager()->createQuery('MATCH (s:Series)-[:HAVE_SUB_SERIES]->(su:SubSeries{name:{subSerName}}) WHERE ID(s) = {serId} return su;');
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
        $series = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\Series')
            ->findOneById($seriesId);

        if (!($series instanceof Series)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists')
            );
        }

        return $series;
    }

    /**
     * @param int $subSeriesId
     * @return SubSeries
     */
    public function getSubSeriesSeriesById(int $subSeriesId)
    {
        $series = $this->getEntityManager()
            ->getRepository('AppBundle\Model\NodeEntity\SubSeries')
            ->findOneById($subSeriesId);

        if (!($series instanceof SubSeries)) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.series.does_not_exists')
            );
        }

        return $series;
    }

    /**
     * @param int $subSeriesId
     */
    public function removeSubSeriesById(int $subSeriesId){
        $subSeries = $this->getSubSeriesSeriesById($subSeriesId);
        $this->getEntityManager()->remove($subSeries, true);
        $this->getEntityManager()->flush();
    }
}