<?php

namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\Util\SpecializationCategory;
use GraphAware\Bolt\Result\Result;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AcademicYearService
 * @package AppBundle\Service
 */
class AcademicYearService
{
    const SERVICE_NAME = 'app.academic_year.service';

    const WEEK_BY_DATE_METHOD = '/get-week-by-date';

    /** @var  string */
    private $apiUrl;

    /**
     * AcademicYearService constructor.
     * @param string $apiUrl
     */
    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }


    /**
     * @param \DateTime $date
     * @param int $yearOfStudy
     *
     * @param string $specialization
     * @see SpecializationCategory
     * @return array
     */
    public function getActivityDetailsOnDate(\DateTime $date, $yearOfStudy, $specialization)
    {
        $client = $this->getClient($this->apiUrl);


        $result = $client->post(static::WEEK_BY_DATE_METHOD, array(
            'json' => array(
                "date" => $date->format('d-m-Y'),
                "year_of_study" => $yearOfStudy,
                "specialization" => $specialization
            )
        ));


        return json_decode($result->getBody()->getContents(), true);
    }

    /**
     * @return Client
     */
    private function getClient($url)
    {
        return new Client([
            // Base URI is used with relative requests
            'base_uri' => $url,
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);
    }
}