<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Model\NodeEntity\Util\WeekType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ActivityInternationalDataService
 * @package AppBundle\Service
 */
class ActivityInternationalDataService
{
    const SERVICE_NAME = 'app.activity_international_data.service';

    /**
     * @param $type
     * @return null|string
     */
    public function getWeekTypeFromRo($type)
    {
        switch ($type) {
            case '':
                return 'every';
            case 'para':
                return WeekType::EVEN;
            case 'impara':
                return WeekType::ODD;
            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid activity repetition rule: ' . $type);
        }
    }

    /**
     * @param $type
     * @return null|string
     */
    public function getActivityTypeFromRo($type)
    {
        switch ($type) {
            case 'curs':
                return ActivityCategory::COURSE;
            case 'laborator':
                return ActivityCategory::LABORATORY;
            case 'seminar':
                return ActivityCategory::SEMINAR;
            case 'examen':
                return ActivityCategory::EXAM;
            case 'colocviu':
                return ActivityCategory::COLLOQUIUM;
            case 'proiect':
                return ActivityCategory::PROJECT;
            default:
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid activity type : ' . $type);
        }
    }
}