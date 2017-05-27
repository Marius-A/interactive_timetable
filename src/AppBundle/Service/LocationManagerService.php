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

    /** @var  Location */
    protected $subject;

    const SERVICE_NAME = 'app.location_manager.service';

    /**
     * @param string $name
     * @return Location
     */
    public function createNew($name)
    {
        $result = $this->getEntityManager()
            ->getRepository(Location::class)
            ->findOneBy(
                array(
                    'name' => $name
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.location_already_exists')
            );
        }

        $subject = new Location();
        $subject->setName($name);

        $this->subject = $subject;

        $this->getEntityManager()->persist($this->subject);
        $this->getEntityManager()->flush();

        return $this->subject;
    }

    /**
     *
     */
    public function removeLocation()
    {
        $result = $this->getEntityManager()
            ->getRepository(Location::class)
            ->findOneBy(
                array(
                    'name' => $this->subject->getName()
                )
            );

        if ($result == null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.location.location_does_not_exists')
            );
        }

        $this->getEntityManager()->remove($this->subject);
        $this->getEntityManager()->flush();
    }
}