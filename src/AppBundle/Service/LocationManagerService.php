<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Location;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * //TODO link location to faculty
 * Class LocationManagerService
 * @package AppBundle\Service
 */
class LocationManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.location_manager.service';

    /**
     * @param string $shortName
     * @return Location
     */
    public function createNew(string $shortName,string $fullName)
    {
        /** @var Location $result */
        $result = $this->getLocationByShortName($shortName);

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.already_exists')
            );
        }

        $location = new Location($shortName, $fullName);

        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();

        return $location;
    }

    /**
     * @param int $locationId
     * @param string $newShortName
     * @param string $newLongName
     */
    public function updateLocationName(int $locationId, string $newShortName, string $newLongName)
    {
        /** @var Location $location */
        $location = $this->getLocationById($locationId);

        $otherLocation1 = $this->getLocationByShortName($newShortName);
        $otherLocation2 = $this->getLocationByFullName($newLongName);

        if ($otherLocation1 != null || $otherLocation2 != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.already_exists')
            );
        }

        if($newShortName != ''){
            $location->setShortName($newShortName);
        }

        if($newLongName != ''){
            $location->setFullName($newLongName);
        }

        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $locationId
     */
    public function removeLocationById(int $locationId)
    {
        /** @var Location $location */
        $location = $this->getLocationById($locationId);

        $this->getEntityManager()->remove($location);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $name
     * @return Location | null
     */
    public function getLocationByShortName(string $name)
    {
        return $this->getEntityManager()
            ->createQuery('MATCH (l:Location) WHERE l.shortName = {name} RETURN l')
            ->addEntityMapping('l', Location::class)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    /**
     * @param string $name
     * @return Location | null
     */
    public function getLocationByFullName(string $name)
    {
        return $this->getEntityManager()
            ->createQuery('MATCH (l:Location) WHERE l.fullName = {name} RETURN l')
            ->addEntityMapping('l', Location::class)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @return Location|null
     */
    public function getLocationById(int $id)
    {
        /** @var Location $location */
        $location = $this->getEntityManager()
            ->getRepository(Location::class)
            ->findOneById($id);

        $this->throwNotFoundExceptionOnNullLocation($location);

        return $location;
    }

    /**
     * @param string $partialName
     * @return Location[]
     */
    public function getLocationsWithNameLike(string $partialName)
    {
        $partialName = '.*'.strtolower($partialName).'.*';
        return $this->getEntityManager()
            ->createQuery("MATCH (l:Location) WHERE toLower(l.shortName) =~ {name} OR toLower(l.fullName) =~ {name} RETURN l")
            ->addEntityMapping('l', Location::class)
            ->setParameter('name', $partialName)
            ->getResult();
    }

    /**
     * @param Location $location
     */
    public function throwNotFoundExceptionOnNullLocation($location)
    {
        if ($location == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.location.does_not_exists')
            );
        }
    }

    /**
     * @param Location $location
     * @param $name
     */
    public function throwNotFoundExceptionOnNullLocationWithName($location, $name)
    {
        if ($location == null) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                $this->getTranslator()->trans('app.warnings.location.does_not_exists').': '.$name
            );
        }
    }
}