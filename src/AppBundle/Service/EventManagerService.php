<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Event;
use AppBundle\Model\NodeEntity\Location;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class EventManagerService
 * @package AppBundle\Service
 */
class EventManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    /** @var  Event */
    protected $subject;

    const SERVICE_NAME = 'app.event_manager.service';


    public function createNew(
        $name, $description, $startDate, $endDate, $location,
        $repeatUntil = null, $recurrenceFrequency = null,
        $recurrenceInterval = null, $recurrenceDay = null
    )
    {

        $result = $this->getEntityManager()
            ->getRepository(Event::class)
            ->findOneBy(
                array(
                    'name' => $name
                )
            );

        if ($result != null) {
            throw new HttpException(
                Response::HTTP_CONFLICT,
                $this->getTranslator()->trans('app.warnings.event.event_already_exists')
            );
        }

        $event = new Event(
            $name, $description, 'ACTIVE', $startDate, $endDate, $location,
            $repeatUntil, $recurrenceFrequency, $recurrenceInterval, $recurrenceDay
        );


        $this->subject = $event;

        $this->getEntityManager()->persist($this->subject);
        $this->getEntityManager()->flush();

        return $this->subject;
    }

    /**
     *
     */
    public function removeEvent()
    {
        $result = $this->getEntityManager()
            ->getRepository(Event::class)
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