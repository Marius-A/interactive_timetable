<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Event;
use AppBundle\Model\NodeEntity\Location;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class EventManagerService
 * @package AppBundle\Service
 */
class EventManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.event_manager.service';

    /**
     * @param string $name
     * @param string $description
     * @param \DateTime $startDate
     * @param Location $location
     * @param \DateTime $endDate
     * @param string $repeatUntil
     * @param string $recurrenceFrequency
     * @param null $recurrenceInterval
     * @param null $recurrenceDay
     *
     * todo check for overlaps
     *
     * @return Event
     */
    public function createNew(
        $name, $description, $startDate, $location, $endDate = null,
        $repeatUntil = null, $recurrenceFrequency = null,
        $recurrenceInterval = null, $recurrenceDay = null
    )
    {

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
                $this->getTranslator()->trans('app.warnings.location.does_not_exists')
            );
        }

        $this->getEntityManager()->remove($this->subject);
        $this->getEntityManager()->flush();
    }
}