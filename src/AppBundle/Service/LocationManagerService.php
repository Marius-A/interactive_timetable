<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Location;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class LocationManagerService
 * @package AppBundle\Service
 */
class LocationManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.location_manager.service';

    /**
     * @param string $name
     * @return Location
     */
    public function createNew($name)
    {
        /** @var Location $result */
        $result = $this->getLocationByName($name);

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.already_exists')
            );
        }

        $location = new Location($name);

        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();

        return $location;
    }

    /**
     * @param int $locationId
     * @param string $newName
     */
    public function updateLocationName(int $locationId, string $newName)
    {
        /** @var Location $location */
        $location = $this->getEntityManager()
            ->getRepository(Location::class)
            ->findOneById($locationId);

        $this->throwNotFoundExceptionOnNullLocation($location);

        $otherLocation = $this->getLocationByName($newName);

        if ($otherLocation != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.already_exists')
            );
        }

        $location->setName($newName);

        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $locationId
     */
    public function removeLocationById(int $locationId)
    {
        /** @var Location $location */
        $location = $this->getEntityManager()
            ->getRepository(Location::class)
            ->findOneById($locationId);

        $this->throwNotFoundExceptionOnNullLocation($location);

        $this->getEntityManager()->remove($location);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $name
     * @return Location | null
     */
    public function getLocationByName(string $name)
    {
        return $this->getEntityManager()
            ->createQuery('MATCH (l:Location) WHERE l.name = {name} RETURN l')
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
            ->createQuery("MATCH (l:Location) WHERE toLower(l.name) =~ {name} RETURN l")
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
}