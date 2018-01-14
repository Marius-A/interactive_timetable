<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\AcademicYear;
use AppBundle\Model\NodeEntity\EvaluationActivity;
use AppBundle\Model\NodeEntity\Location;
use AppBundle\Model\NodeEntity\Participant;
use AppBundle\Model\NodeEntity\Semester;
use AppBundle\Model\NodeEntity\Subject;
use AppBundle\Model\NodeEntity\Teacher;
use AppBundle\Model\NodeEntity\TeachingActivity;
use AppBundle\Model\NodeEntity\Util\ActivityType;
use AppBundle\Model\NodeEntity\Util\EvaluationActivityType;
use AppBundle\Model\NodeEntity\Util\ParticipantType;
use AppBundle\Service\Traits\EntityManagerTrait;
use AppBundle\Service\Traits\TranslatorTrait;
use GraphAware\Common\Type\Node;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Neo4j\OGM\Query;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ActivityManagerService
 * @package AppBundle\Service
 */
class ActivityManagerService
{
    use EntityManagerTrait;
    use TranslatorTrait;

    const SERVICE_NAME = 'app.activity_manager.service';

    /** @var  LocationManagerService */
    protected $locationManager;
    /** @var  AcademicYearManagerService */
    protected $academicYearManager;
    /** @var  SubjectManagerService */
    protected $subjectManager;
    /** @var  TeacherManagerService */
    private $teacherManager;
    /** @var  SeriesManagerService */
    private $seriesManager;
    /** @var  SpecializationManagerService */
    private $specializationManager;
    /** @var  Serializer */
    private $serializer;
    /** @var  AcademicYearService */
    private $academicYearService;
    /** @var  ParticipantManagerService */
    private $participantManager;
    /** @var  StudentManagerService */
    private $studentManager;

    /** @var  ActivityOverlapsCheckerService */
    private $activityOverlapsChecker;
    /** @var  ActivityInternationalDataService */
    private $activityInternationalDate;

    /**
     * ActivityManagerService constructor.
     * @param LocationManagerService $locationManager
     * @param AcademicYearManagerService $academicYearManager
     * @param SubjectManagerService $subjectManager
     * @param TeacherManagerService $teacherManager
     * @param SeriesManagerService $seriesManager
     * @param SpecializationManagerService $specializationManager
     * @param AcademicYearService $yearService
     * @param ParticipantManagerService $participantManager
     * @param StudentManagerService $studentManagerService
     * @param ActivityOverlapsCheckerService $activityOverlapsChecker
     * @param ActivityInternationalDataService $activityInternationalData
     * @param Serializer $serializer
     */
    public function __construct(
        LocationManagerService $locationManager, AcademicYearManagerService $academicYearManager,
        SubjectManagerService $subjectManager, TeacherManagerService $teacherManager,
        SeriesManagerService $seriesManager, SpecializationManagerService $specializationManager,
        AcademicYearService $yearService,
        ParticipantManagerService $participantManager,
        StudentManagerService $studentManagerService,
        ActivityOverlapsCheckerService $activityOverlapsChecker,
        ActivityInternationalDataService $activityInternationalData,
        Serializer $serializer
    )
    {
        $this->locationManager = $locationManager;
        $this->academicYearManager = $academicYearManager;
        $this->academicYearService = $yearService;
        $this->subjectManager = $subjectManager;
        $this->teacherManager = $teacherManager;
        $this->seriesManager = $seriesManager;
        $this->specializationManager = $specializationManager;
        $this->participantManager = $participantManager;
        $this->studentManager = $studentManagerService;
        $this->activityOverlapsChecker = $activityOverlapsChecker;
        $this->activityInternationalDate = $activityInternationalData;
        $this->serializer = $serializer;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getTeachingActivityDetailsById(int $id)
    {
        $result = $this->getEntityManager()
            ->createQuery(' MATCH (a:TeachingActivity) where ID(a) = {actId} return a;')
            ->setParameter('actId', $id)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNull($result);

        return $this->getPropertiesFromTeachingActivityNode($result[0]['a']);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getEvaluationActivityDetailsById(int $id)
    {
        $result = $this->getEntityManager()
            ->createQuery(' MATCH (a:EvaluationActivity) where ID(a) = {actId} return a;')
            ->setParameter('actId', $id)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNull($result);

        return $this->getPropertiesFromEvaluationActivityNode($result[0]['a']);
    }

    /**
     * @param int $id
     * @return TeachingActivity
     */
    public function getTeachingActivityById(int $id)
    {
        /** @var TeachingActivity $result */
        $result = $this->getEntityManager()
            ->getRepository(TeachingActivity::class)
            ->findOneById($id);

        $this->throwNotFoundExceptionOnNull($result);

        return $result;
    }

    /**
     * @param int $id
     * @return EvaluationActivity
     */
    public function getEvaluationActivityById(int $id)
    {
        /** @var EvaluationActivity $result */
        $result = $this->getEntityManager()
            ->getRepository(EvaluationActivity::class)
            ->findOneById($id);

        $this->throwNotFoundExceptionOnNull($result);

        return $result;
    }

    public function createTeachingActivity(
        string $activityCategory,
        string $academicYearName,
        int $semesterNumber,
        string $weekType,
        int $day,
        int $hour,
        int $duration,
        int $teacherId,
        int $subjectId,
        int $locationId,
        $participantsId
    )
    {

        $location = $this->locationManager->getLocationById($locationId);
        $academicYear = $this->academicYearManager->getAcademicYearByName($academicYearName);
        $semester = $this->academicYearManager->getSemesterByAcademicYearAndNumber($academicYear, $semesterNumber);
        $subject = $this->subjectManager->getSubjectById($subjectId);
        $teacher = $this->teacherManager->getTeacherById($teacherId);
        $participants = $this->getParticipants($participantsId);

        $this->createAndPersistTeachingActivity($location, $activityCategory, $semester, $weekType, $day, $hour, $duration, $teacher, $subject, $participants);
    }

    /**
     * @param int $activityId
     * @param array $changes
     */
    public function updateTeachingActivity(int $activityId, array $changes)
    {
        if (empty($changes)) {
            return;
        }

        /** @var TeachingActivity $activity */
        $activity = $this->getTeachingActivityById($activityId);

        if (!is_null($changes['participants']) && !empty($changes['participants'])) {
            $activity->setParticipants(
                $this->participantManager->getParticipantsByIds($changes['participants'])
            );
            $this->removeOldParticipantsRelation($activity->getId());
        }
        if (!is_null($changes['teacher'])) {
            $activity->setTeacher(
                $this->teacherManager->getTeacherById($changes['teacher'])
            );
            $this->removeOldTeacherRelation($activity->getId());
        }
        if (!is_null($changes['activityCategory'])) {
            $activity->setActivityCategory($changes['activityCategory']);
        }
        if (!is_null($changes['academicYear'])) {
            $semester = $this->academicYearManager->getSemesterByActivityId($activity->getId());
            $activity->setSemester(
                $this->academicYearManager
                    ->getSemesterByAcademicYearNameAndNumber(
                        $changes['academicYear'],
                        $semester['number']
                    )
            );
            $this->removeOldSemesterRelation($activity->getId());
        }
        if (!is_null($changes['semesterNumber'])) {
            $semester = $this->academicYearManager->getSemesterByActivityId($activity->getId());
            $activity->setSemester(
                $this->academicYearManager
                    ->getSemesterByAcademicYearNameAndNumber(
                        $semester['academicYear']['name'],
                        $changes['semesterNumber']
                    )
            );
            $this->removeOldSemesterRelation($activity->getId());
        }
        if (!is_null($changes['day'])) {
            $activity->setDay($changes['day']);
        }
        if (!is_null($changes['hour'])) {
            $activity->setHour($changes['hour']);
        }
        if (!is_null($changes['subject'])) {
            $activity->setSubject(
                $this->subjectManager->getSubjectById($changes['subject'])
            );
            $this->removeOldSubjectRelation($activity->getId());
        }
        if (!is_null($changes['location'])) {
            $activity->setLocation(
                $this->locationManager->getLocationById($changes['location'])
            );
            $this->removeOldLocationRelation($activity->getId());
        }
        if (!is_null($changes['weekType'])) {
            if ($changes['weekType'] != $activity->getWeekType()) {
                $activity->setWeekType($changes['weekType']);
            }
        }
        if (!is_null($changes['duration'])) {
            if ($changes['duration'] != $activity->getDuration()) {
                $activity->setDuration($changes['duration']);
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param int $activityId
     * @param array $changes
     */
    public function updateEvaluationActivity(int $activityId, array $changes)
    {
        if (empty($changes)) {
            return;
        }

        /** @var EvaluationActivity $activity */
        $activity = $this->getEvaluationActivityById($activityId);

        if (!is_null($changes['participants']) && !empty($changes['participants'])) {
            $activity->setParticipants(
                $this->participantManager->getParticipantsByIds($changes['participants'])
            );
            $this->removeOldParticipantsRelation($activity->getId());
        }
        if (!is_null($changes['teacher'])) {
            $activity->setTeacher(
                $this->teacherManager->getTeacherById($changes['teacher'])
            );
            $this->removeOldTeacherSupervisorRelation($activity->getId());
        }
        if (!is_null($changes['activityCategory'])) {
            $activity->setActivityCategory($changes['activityCategory']);
        }

        if (!is_null($changes['date'])) {
            $date = new \DateTime($changes['date']);
            $activity->setDate($date);
        }
        if (!is_null($changes['hour'])) {
            $activity->setHour($changes['hour']);
        }
        if (!is_null($changes['subject'])) {
            $activity->setSubject(
                $this->subjectManager->getSubjectById($changes['subject'])
            );
            $this->removeOldSubjectRelation($activity->getId());
        }
        if (!is_null($changes['academicYear'])) {
            $activity->setAcademicYear(
                $this->academicYearManager->getAcademicYearByName($changes['academicYear'])
            );
            $this->removeOldAcademicYearRelation($activity->getId());
        }
        if (!is_null($changes['location'])) {
            $activity->setLocation(
                $this->locationManager->getLocationById($changes['location'])
            );
            $this->removeOldLocationRelation($activity->getId());
        }
        if (!is_null($changes['duration'])) {
            if ($changes['duration'] != $activity->getDuration()) {
                $activity->setDuration($changes['duration']);
            }
        }
        if (!is_null($changes['type'])) {
            $type = $changes['type'];
            if (!EvaluationActivityType::isValidValue($type)) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Available types: ' . EvaluationActivityType::EXAM, ', ' . EvaluationActivityType::RESTANTA);
            }

            $activity->setType($type);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Location $location
     * @param $activityCategory
     * @param Semester $semester
     * @param string $weekType
     * @param string $day
     * @param int $hour
     * @param int $duration
     * @param Teacher $teacher
     * @param Subject $subject
     * @param $participants
     */
    private function createAndPersistTeachingActivity(
        Location $location,
        $activityCategory,
        Semester $semester,
        string $weekType,
        string $day,
        int $hour,
        int $duration,
        Teacher $teacher,
        Subject $subject,
        $participants
    )
    {
        $activity = new TeachingActivity($location, $activityCategory, $semester, $weekType, $day, $hour, $duration, $teacher, $subject);
        $activity->setParticipants($participants);
        //todo implement overlaps verification engine

        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush();
    }


    public function createAndPersistEvaluationActivity(
        string $activityCategory,
        string $type,
        string $academicYearName,
        int $locationId,
        int $subjectId,
        int $teacherId,
        \DateTime $date,
        int $hour,
        int $duration,
        array $participantsId
    )
    {

        if (!EvaluationActivityType::isValidValue($type)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Available types: ' . EvaluationActivityType::EXAM, ', ' . EvaluationActivityType::RESTANTA);
        }

        $academicYear = $this->academicYearManager->getAcademicYearByName($academicYearName);
        $subject = $this->subjectManager->getSubjectById($subjectId);
        $location = $this->locationManager->getLocationById($locationId);
        $participants = $this->participantManager->getParticipantsByIds($participantsId);
        $teacher = $this->teacherManager->getTeacherById($teacherId);

        $activity = new EvaluationActivity(
            $location,
            $activityCategory,
            $type,
            $hour,
            $date,
            $duration,
            $subject,
            $teacher,
            $academicYear
        );

        $activity->setParticipants($participants);

        $this->getEntityManager()->persist($activity);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $academicYear
     * @param int $semesterNumber
     * @param int $specializationId
     * @return array
     */
    public function getAllActivitiesForSemesterAndSpecialization(string $academicYear, int $semesterNumber, int $specializationId)
    {
        $specialization = $this->specializationManager->getSpecializationById($specializationId);
        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

        $result = $this->getEntityManager()
            ->createQuery(' MATCH (spec:Specialization)<-[r:PART_OF*]-(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_SEMESTER]->(sem:Semester) where ID(sem) = {semId} AND ID(spec) = {specId} return a;')
            ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
            ->setParameter('semId', $semester->getId())
            ->setParameter('specId', $specialization->getId())
            ->getResult();


        $activities = array();
        foreach ($result as $act) {
            $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
        }

        return $activities;
    }


    /**
     * @param string $academicYearName
     * @param int $specializationId
     * @param string $type
     * @return array
     */
    public function getAllEvaluationActivitiesByAcademicYearAndSpecialization(string $academicYearName, int $specializationId, string $type)
    {
        $specialization = $this->specializationManager->getSpecializationById($specializationId);
        $academicYear = $this->academicYearManager->getAcademicYearByName($academicYearName);

        $result = $this->getEntityManager()
            ->createQuery('MATCH (spec:Specialization)<-[r:PART_OF*]-(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_YEARS]->(year:AcademicYear) where ID(year) = {yearId} AND a.type = {type} AND ID(spec) = {specId} return a;')
            ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
            ->setParameter('specId', $specialization->getId())
            ->setParameter('type', $type)
            ->setParameter('yearId', $academicYear->getId())
            ->getResult();


        $activities = array();
        foreach ($result as $act) {
            $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
        }

        return $activities;
    }

    /**
     * @param int $participantId
     * @param \DateTime $date
     * @return array
     */
    public function getActivitiesForParticipantOnDate(int $participantId, \DateTime $date)
    {
        $participant = $this->specializationManager->getSpecializationById($participantId);

        $currentActivities = null;
        try {
            $currentActivities = $this->academicYearService->getActivityDetailsOnDate(
                $date
            );
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $nextWeekActivities = null;
        try {
            $nextWeekDate = new \DateTime($date->format('d-m-Y'));
            $nextWeekDate->modify('+7 day');
            $nextWeekActivities = $this->academicYearService->getActivityDetailsOnDate(
                $nextWeekDate
            );
        } catch (\Exception $exception) {
        }

        $startDate = new \DateTime($date->format('d-m-Y'));
        $endDate = new \DateTime($date->format('d-m-Y'));
        $endDate->modify('+7 day');


        $teachingActivitiesQueries = array();
        $evaluationActivitiesQueries = array();


        foreach ($currentActivities as $index => $academicActivity) {

            $weekNumber = $academicActivity['weekNumber'];
            $activityType = $academicActivity['activity']['activityType'];

            $semesterDetails = $academicActivity['activity']['activityGroup']['semester'];
            $academicYear = $semesterDetails['academicYear']['years'];
            $semesterNumber = $semesterDetails['number'];
            $activityYearsOfStudy = $academicActivity['activity']['activityGroup']['yearOfStudy'];

            $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);


            switch ($activityType) {
                case ActivityType::TEACHING:

                    $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';

                    $query = 'MATCH (p:Participant)-[:PART_OF*0..]->(part:Participant)-[:PARTICIPATE]->(a:TeachingActivity)-[:ON_SEMESTER]->(sem:Semester)';
                    $query .= ' WHERE ID(sem) =' . $semester->getId() . ' AND (a.weekType = \'' . $weekType . '\' or a.weekType = \'every\')';
                    $query .= ' AND  ID(p) = {partId}  ';
                    $query .= 'WITH a,p,part';


                    $query .= ' MATCH (p)-[:PART_OF*0..]->(s:Series)-[:PART_OF*0..]->(sp:Specialization) WHERE';

                    if (!empty($activityYearsOfStudy['license'])) {
                        $query .= "(( s.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['license']) . "] AND sp.specializationCategory = 'licenta') ";
                    }
                    if (!empty($activityYearsOfStudy['master'])) {
                        !empty($activityYearsOfStudy['license']) ? $query .= ' OR ' : $x = 1;

                        $query .= " (s.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['master']) . "] AND sp.specializationCategory = 'master') ";
                    }

                    $query .= ')';


                    $query .= ' RETURN a';


                    $teachingActivitiesQueries[] = $query;


                    if (
                        !is_null($nextWeekActivities) &&
                        isset($nextWeekActivities[$index]) &&
                        $nextWeekActivities[$index]['activity']['activityType'] == ActivityType::EXAMINATION
                    ) {
                        goto examination_queries;
                    }

                    break;
                case ActivityType::EXAMINATION:
                    examination_queries:


                    $query = ' MATCH (part)-[re:PART_OF*0..]->(p:Participant)-[:PARTICIPATE]->(a:EvaluationActivity)';
                    $query .= ' WHERE (a.date > {start} AND a.date < {end}) ';
                    $query .= ' AND ID(part) = {partId}';
                    $query .= ' RETURN a';
                    $evaluationActivitiesQueries[] = $query;

                    break;
                case ActivityType::PRACTICE:
                    break;
                case ActivityType::FREE_TIME:
                    break;
                default:
                    dump($activityType);
                    die;
            }

        }

        $teachingActivitiesQuery = implode(' UNION ', $teachingActivitiesQueries);
        $evaluationActivitiesQuery = implode(' UNION ', $evaluationActivitiesQueries);


//        dump($startDate->getTimestamp() * 1000);
//        dump($endDate->getTimestamp() * 1000);
//        dump($evaluationActivitiesQueries);
//        dump($teachingActivitiesQueries);die;


        $activities = array();
        if (!empty($teachingActivitiesQueries)) {
            $teachingActivities = $this->getEntityManager()
                ->createQuery($teachingActivitiesQuery)
                ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                ->setParameter('partId', $participant->getId())
                ->getResult();

            foreach ($teachingActivities as $act) {
                $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
            }
        }

        if (!empty($evaluationActivitiesQueries)) {

            $evaluationActivities = $this->getEntityManager()
                ->createQuery($evaluationActivitiesQuery)
                ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
                ->setParameter('partId', $participant->getId())
                ->setParameter('start', $startDate->getTimestamp() * 1000)
                ->setParameter('end', $endDate->getTimestamp() * 1000)
                ->getResult();

            foreach ($evaluationActivities as $act) {
                $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
            }

        }

        return $activities;
    }

    /**
     * @param int $studentId
     * @param \DateTime $date
     * @return array
     */
    public function getAllWeekActivitiesForStudentOnDate(int $studentId, \DateTime $date)
    {
        $student = $this->studentManager->getStudentById($studentId);

        $series = $this->seriesManager->getSeriesBySubSeriesId($student->getSubSeries()->getId());

        $currentData = null;
        try {
            $currentData = $this->academicYearService->getActivityDetailForYearOfStudyOnDate(
                $date,
                $series->getYearOfStudy(),
                $series->getSpecialization()->getSpecializationCategory()
            );

        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $nextWeekData = null;
        try {
            $nextWeekDate = new \DateTime($date->format('d-m-Y'));
            $nextWeekDate->modify('+7 day');
            $nextWeekData = $this->academicYearService->getActivityDetailForYearOfStudyOnDate(
                $nextWeekDate,
                $series->getYearOfStudy(),
                $series->getSpecialization()->getSpecializationCategory()
            );
        } catch (\Exception $exception) {
        }

        $weekNumber = $currentData['weekNumber'];
        $correspondingActivity = $currentData['activity']['activityType'];

        $semesterDetails = $currentData['activity']['activityGroup']['semester'];
        $academicYear = $semesterDetails['academicYear']['years'];
        $semesterNumber = $semesterDetails['number'];

        $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';


        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

        $teachingActivities = array();
        $evaluationActivities = array();


        switch ($correspondingActivity) {
            case ActivityType::TEACHING:
//                $dayNumber = (int) date('w', $date->getTimestamp());
                $teachingActivities = $this->getEntityManager()
                    ->createQuery('MATCH (spec:Participant)-[r:PART_OF*0..]->(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_SEMESTER]->(sem:Semester) where ID(sem) = {semId} AND ID(spec) = {specId} AND (a.weekType = \'every\' OR  a.weekType = {weekT})  return a;')
                    ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                    ->setParameter('semId', $semester->getId())
                    ->setParameter('specId', $student->getSubSeries()->getId())
//                    ->setParameter('dayNum', $dayNumber)
                    ->setParameter('weekT', $weekType)
                    ->getResult();
                if (!is_null($nextWeekData) && $nextWeekData['activity']['activityType'] == 'EXAMINARE') {
                    goto check_for_exams;
                }
                break;
            case ActivityType::EXAMINATION:
                check_for_exams:

                $startDate = new \DateTime($date->format('d-m-Y'));
                $endDate = new \DateTime($date->format('d-m-Y'));
                $endDate->modify('+7 day');
//
//                dump($startDate->getTimestamp() * 1000);
//                dump($endDate->getTimestamp() * 1000);die;

                $evaluationActivities = $this->getEntityManager()
                    ->createQuery('MATCH (spec:Participant)-[r:PART_OF*0..]->(p:Participant)-[:PARTICIPATE]->(a:EvaluationActivity) where ID(spec) = {specId} AND (a.date > {start} AND a.date < {end})  return a;')
                    ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
                    ->setParameter('specId', $student->getSubSeries()->getId())
                    ->setParameter('start', $startDate->getTimestamp() * 1000)
                    ->setParameter('end', $endDate->getTimestamp() * 1000)
                    ->getResult();
                 break;

            case ActivityType::PRACTICE:

                return array(
                    array(
                        'activityCategory' => 'practice',
                        "activityName" => $currentData['activity']['activityName'],
                        'period' => $currentData['activity']['period']
                    )
                );
                break;

            case ActivityType::FREE_TIME:
                return array();
                break;
            default:
                throw new HttpException(Response::HTTP_NOT_FOUND, 'No activities were founded on this day');
        }


        $activities = array();

        foreach ($teachingActivities as $act) {
            $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
        }
        foreach ($evaluationActivities as $act) {
            $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
        }

        return $activities;

    }

    /**
     * @param int $teacherId
     * @param \DateTime $date
     * @return array
     */
    public function getActivitiesForTeacherOnDate(int $teacherId, \DateTime $date)
    {
        $teacher = $this->teacherManager->getTeacherById($teacherId);

        $currentActivities = null;
        try {
            $currentActivities = $this->academicYearService->getActivityDetailsOnDate(
                $date
            );
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $nextWeekActivities = null;
        try {
            $nextWeekDate = new \DateTime($date->format('d-m-Y'));
            $nextWeekDate->modify('+7 day');
            $nextWeekActivities = $this->academicYearService->getActivityDetailsOnDate(
                $nextWeekDate
            );
        } catch (\Exception $exception) {
        }

        $startDate = new \DateTime($date->format('d-m-Y'));
        $endDate = new \DateTime($date->format('d-m-Y'));
        $endDate->modify('+7 day');


        $teachingActivitiesQueries = array();
        $evaluationActivitiesQueries = array();


        foreach ($currentActivities as $index => $academicActivity) {

            $weekNumber = $academicActivity['weekNumber'];
            $activityType = $academicActivity['activity']['activityType'];

            $semesterDetails = $academicActivity['activity']['activityGroup']['semester'];
            $academicYear = $semesterDetails['academicYear']['years'];
            $semesterNumber = $semesterDetails['number'];
            $activityYearsOfStudy = $academicActivity['activity']['activityGroup']['yearOfStudy'];

            $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);


            switch ($activityType) {
                case ActivityType::TEACHING:



                    $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';

                    $query = 'MATCH (t:Teacher)-[:TEACHED_BY]-(a:TeachingActivity) where ID(t) = {tId} WITH a ';
                    $query .= 'MATCH (spec:Specialization)-[r:PART_OF]-(se:Series)-[re:PART_OF*0..]-(p:Participant)-[:PARTICIPATE]->(a)-[:ON_SEMESTER]->(sem:Semester) ';
                    $query .= ' WHERE ID(sem) =' . $semester->getId() . ' AND (a.weekType = \'' . $weekType . '\' or a.weekType = \'every\')';

                    $query .= ' AND (';

                    if (!empty($activityYearsOfStudy['license'])) {
                        $query .= "( se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['license']) . "] AND spec.specializationCategory = 'licenta') ";
                    }
                    if (!empty($activityYearsOfStudy['master'])) {
                        !empty($activityYearsOfStudy['license']) ? $query .= ' OR ' : $x = 1;

                        $query .= " (se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['master']) . "] AND spec.specializationCategory = 'master') ";
                    }

                    $query .= ') RETURN a';

                    $teachingActivitiesQueries[] = $query;


                    if (
                        !is_null($nextWeekActivities) &&
                        isset($nextWeekActivities[$index]) &&
                        $nextWeekActivities[$index]['activity']['activityType'] == ActivityType::EXAMINATION
                    ) {
                        goto examination_queries;
                    }

                    break;
                case ActivityType::EXAMINATION:
                    examination_queries:


                    $query = 'MATCH (t:Teacher)<-[:SUPERVISED_BY]-(a:EvaluationActivity)-[:ON_YEARS]->(y:AcademicYear) ';
                    $query .= ' WHERE (a.date > {start} AND a.date < {end}) AND ID(t) = {tId} AND y.name = \'' . $academicYear . '\'';
                    $query .= ' RETURN a';
                    $evaluationActivitiesQueries[] = $query;

                    $query = 'MATCH (t:Teacher)-[:ASSIST]-(a:EvaluationActivity)-[:ON_YEARS]->(y:AcademicYear)';
                    $query .= ' WHERE (a.date > {start} AND a.date < {end}) AND ID(t) = {tId} AND y.name = \'' . $academicYear . '\'';
                    $query .= ' RETURN a';
                    $evaluationActivitiesQueries[] = $query;

                    break;
                case ActivityType::PRACTICE:
                    break;
                case ActivityType::FREE_TIME:
                    break;
                default:
                    dump($activityType);
                    die;
            }

        }


        $teachingActivitiesQuery = implode(' UNION ', $teachingActivitiesQueries);
        $evaluationActivitiesQuery = implode(' UNION ', $evaluationActivitiesQueries);

//        dump($teachingActivitiesQueries);
//        dump($evaluationActivitiesQueries);die;

//        dump($startDate->getTimestamp() * 1000);
//        dump($endDate->getTimestamp() * 1000);die;

        $activities = array();
        if (!empty($teachingActivitiesQueries)) {
            $teachingActivities = $this->getEntityManager()
                ->createQuery($teachingActivitiesQuery)
                ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                ->setParameter('tId', $teacher->getId())
                ->getResult();

            foreach ($teachingActivities as $act) {
                $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
            }
        }

        if (!empty($evaluationActivitiesQueries)) {

            $evaluationActivities = $this->getEntityManager()
                ->createQuery($evaluationActivitiesQuery)
                ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
                ->setParameter('tId', $teacher->getId())
                ->setParameter('start', $startDate->getTimestamp() * 1000)
                ->setParameter('end', $endDate->getTimestamp() * 1000)
                ->getResult();

            foreach ($evaluationActivities as $act) {
                $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
            }

        }

        return $activities;
    }

    /**
     * @param int $locationId
     * @param \DateTime $date
     * @return array
     */
    public function getActivitiesForLocationOnDate(int $locationId, \DateTime $date)
    {
        $location = $this->locationManager->getLocationById($locationId);

        $currentActivities = null;
        try {
            $currentActivities = $this->academicYearService->getActivityDetailsOnDate(
                $date
            );
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $nextWeekActivities = null;
        try {
            $nextWeekDate = new \DateTime($date->format('d-m-Y'));
            $nextWeekDate->modify('+7 day');
            $nextWeekActivities = $this->academicYearService->getActivityDetailsOnDate(
                $nextWeekDate
            );
        } catch (\Exception $exception) {
        }

        $startDate = new \DateTime($date->format('d-m-Y'));
        $endDate = new \DateTime($date->format('d-m-Y'));
        $endDate->modify('+7 day');


        $teachingActivitiesQueries = array();
        $evaluationActivitiesQueries = array();


        foreach ($currentActivities as $index => $academicActivity) {

            $weekNumber = $academicActivity['weekNumber'];
            $activityType = $academicActivity['activity']['activityType'];

            $semesterDetails = $academicActivity['activity']['activityGroup']['semester'];
            $academicYear = $semesterDetails['academicYear']['years'];
            $semesterNumber = $semesterDetails['number'];
            $activityYearsOfStudy = $academicActivity['activity']['activityGroup']['yearOfStudy'];

            $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

            switch ($activityType) {
                case ActivityType::TEACHING:

                    $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';


                    //TODO check where to put union
                    $query = 'MATCH (l:Location)-[:IN]-(a:TeachingActivity) where ID(l) = {lId} WITH a ';
                    $query .= 'MATCH (spec:Specialization)-[r:PART_OF]-(se:Series)-[re:PART_OF*0..]-(p:Participant)-[:PARTICIPATE]->(a)-[:ON_SEMESTER]->(sem:Semester) ';
                    $query .= ' WHERE ID(sem) =' . $semester->getId() . ' AND (a.weekType = \'' . $weekType . '\' or a.weekType = \'every\')';

                    $query .= ' AND (';

                    if (!empty($activityYearsOfStudy['license'])) {
                        $query .= "( se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['license']) . "] AND spec.specializationCategory = 'licenta') ";
                    }
                    if (!empty($activityYearsOfStudy['master'])) {
                        !empty($activityYearsOfStudy['license']) ? $query .= ' OR ' : $x = 1;

                        $query .= " (se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['master']) . "] AND spec.specializationCategory = 'master') ";
                    }

                    $query .= ') RETURN a';

                    $teachingActivitiesQueries[] = $query;


                    if (
                        !is_null($nextWeekActivities) &&
                        isset($nextWeekActivities[$index]) &&
                        $nextWeekActivities[$index]['activity']['activityType'] == ActivityType::EXAMINATION
                    ) {
                        goto examination_queries;
                    }

                    break;
                case ActivityType::EXAMINATION:
                    examination_queries:


                    $query = 'MATCH (l:Location)<-[:IN]-(a:EvaluationActivity)-[:ON_YEARS]->(y:AcademicYear) ';
                    $query .= ' WHERE (a.date > {start} AND a.date < {end}) AND ID(l) = {lId} AND y.name = \'' . $academicYear . '\'';
                    $query .= ' RETURN a';
                    $evaluationActivitiesQueries[] = $query;

                    break;
                case ActivityType::PRACTICE:
                    break;
                case ActivityType::FREE_TIME:
                    break;
                default:
                    dump($activityType);
                    die;
            }

        }


        $teachingActivitiesQuery = implode(' UNION ', $teachingActivitiesQueries);
        $evaluationActivitiesQuery = implode(' UNION ', $evaluationActivitiesQueries);

        $activities = array();
        if (!empty($teachingActivitiesQueries)) {
            $teachingActivities = $this->getEntityManager()
                ->createQuery($teachingActivitiesQuery)
                ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                ->setParameter('lId', $location->getId())
                ->getResult();

            foreach ($teachingActivities as $act) {
                $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
            }
        }

        if (!empty($evaluationActivitiesQueries)) {

            $evaluationActivities = $this->getEntityManager()
                ->createQuery($evaluationActivitiesQuery)
                ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
                ->setParameter('lId', $location->getId())
                ->setParameter('start', $startDate->getTimestamp() * 1000)
                ->setParameter('end', $endDate->getTimestamp() * 1000)
                ->getResult();

            foreach ($evaluationActivities as $act) {
                $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
            }

        }

        return $activities;
    }

    /**
     * @param int $subjectId
     * @param \DateTime $date
     * @return array
     */
    public function getActivitiesForSubjectOnDate(int $subjectId, \DateTime $date)
    {
        $subject = $this->subjectManager->getSubjectById($subjectId);

        $currentActivities = null;
        try {
            $currentActivities = $this->academicYearService->getActivityDetailsOnDate(
                $date
            );
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $nextWeekActivities = null;
        try {
            $nextWeekDate = new \DateTime($date->format('d-m-Y'));
            $nextWeekDate->modify('+7 day');
            $nextWeekActivities = $this->academicYearService->getActivityDetailsOnDate(
                $nextWeekDate
            );
        } catch (\Exception $exception) {
        }

        $startDate = new \DateTime($date->format('d-m-Y'));
        $endDate = new \DateTime($date->format('d-m-Y'));
        $endDate->modify('+7 day');


        $teachingActivitiesQueries = array();
        $evaluationActivitiesQueries = array();


        foreach ($currentActivities as $index => $academicActivity) {

            $weekNumber = $academicActivity['weekNumber'];
            $activityType = $academicActivity['activity']['activityType'];

            $semesterDetails = $academicActivity['activity']['activityGroup']['semester'];
            $academicYear = $semesterDetails['academicYear']['years'];
            $semesterNumber = $semesterDetails['number'];
            $activityYearsOfStudy = $academicActivity['activity']['activityGroup']['yearOfStudy'];

            $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

            switch ($activityType) {
                case ActivityType::TEACHING:

                    $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';

                    $query = 'MATCH (su:Subject)-[:LINKED_TO]-(a:TeachingActivity) where ID(su) = {suId} WITH a ';
                    $query .= 'MATCH (spec:Specialization)-[r:PART_OF]-(se:Series)-[re:PART_OF*0..]-(p:Participant)-[:PARTICIPATE]->(a)-[:ON_SEMESTER]->(sem:Semester) ';
                    $query .= ' WHERE ID(sem) =' . $semester->getId() . ' AND (a.weekType = \'' . $weekType . '\' OR a.weekType = \'every\')';

                    $query .= ' AND (';

                    if (!empty($activityYearsOfStudy['license'])) {
                        $query .= "( se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['license']) . "] AND spec.specializationCategory = 'licenta') ";
                    }
                    if (!empty($activityYearsOfStudy['master'])) {
                        !empty($activityYearsOfStudy['license']) ? $query .= ' OR ' : $x = 1;

                        $query .= " (se.yearOfStudy IN[" . implode(',', $activityYearsOfStudy['master']) . "] AND spec.specializationCategory = 'master') ";
                    }

                    $query .= ') RETURN a';

                    $teachingActivitiesQueries[] = $query;


                    if (
                        !is_null($nextWeekActivities) &&
                        isset($nextWeekActivities[$index]) &&
                        $nextWeekActivities[$index]['activity']['activityType'] == ActivityType::EXAMINATION
                    ) {
                        goto examination_queries;
                    }

                    break;
                case ActivityType::EXAMINATION:
                    examination_queries:


                    $query = 'MATCH (su:Subject)-[:LINKED_TO]-(a:EvaluationActivity)-[:ON_YEARS]->(y:AcademicYear) ';
                    $query .= ' WHERE (a.date > {start} AND a.date < {end}) AND ID(su) = {suId} AND y.name = \'' . $academicYear . '\'';
                    $query .= ' RETURN a';
                    $evaluationActivitiesQueries[] = $query;

                    break;
                case ActivityType::PRACTICE:
                    break;
                case ActivityType::FREE_TIME:
                    break;
                default:
                    dump($activityType);
                    die;
            }

        }


        $teachingActivitiesQuery = implode(' UNION ', $teachingActivitiesQueries);
        $evaluationActivitiesQuery = implode(' UNION ', $evaluationActivitiesQueries);


        $activities = array();
        if (!empty($teachingActivitiesQueries)) {
            $teachingActivities = $this->getEntityManager()
                ->createQuery($teachingActivitiesQuery)
                ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                ->setParameter('suId', $subject->getId())
                ->getResult();

            foreach ($teachingActivities as $act) {
                $activities[] = $this->getPropertiesFromTeachingActivityNode($act['a']);
            }
        }

        if (!empty($evaluationActivitiesQueries)) {

            $evaluationActivities = $this->getEntityManager()
                ->createQuery($evaluationActivitiesQuery)
                ->addEntityMapping('a', EvaluationActivity::class, Query::HYDRATE_RAW)
                ->setParameter('suId', $subject->getId())
                ->setParameter('start', $startDate->getTimestamp() * 1000)
                ->setParameter('end', $endDate->getTimestamp() * 1000)
                ->getResult();

            foreach ($evaluationActivities as $act) {
                $activities[] = $this->getPropertiesFromEvaluationActivityNode($act['a']);
            }

        }

        return $activities;
    }

    /**
     * @param $participantsId
     * @return Participant[] | Collection
     */
    private function getParticipants($participantsId)
    {
        $participants = new Collection();

        foreach ($participantsId as $participant) {
            $participants->add($this->getParticipantByTypeAndId($participant['type'], $participant['id']));
        }

        return $participants;
    }

    /**
     * @param $type
     * @param $id
     * @return Participant
     */
    private function getParticipantByTypeAndId($type, $id)
    {
        if (!ParticipantType::isValidValue(strtolower($type))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid participant type:' . $type);
        }
        $participant = null;

        switch ($type) {
            case ParticipantType::SERIES:
                $participant = $this->seriesManager->getSeriesById($id);
                break;
            case ParticipantType::SUB_SERIES:
                $participant = $this->seriesManager->getSubSeriesById($id);
                break;
        }

        return $participant;
    }

    /**
     * @param int $activityId
     */
    private function removeOldLocationRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:IN]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldSubjectRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:LINKED_TO]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldTeacherRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:TEACHED_BY]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldTeacherSupervisorRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:SUPERVISED_BY]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldSemesterRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:ON_SEMESTER]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldAcademicYearRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:ON_YEARS]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldParticipantsRelation($activityId)
    {
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:PARTICIPATE]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }


    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromTeachingActivityNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['semester'] = $this->academicYearManager->getSemesterByActivityId($id);
        $values['teacher'] = $this->teacherManager->getTeacherByTeachingActivityId($id);
        $values['subject'] = $this->subjectManager->getSubjectByActivityId($id);
        $values['participants'] = $this->participantManager->getParticipantsByActivityId($id);
        $values['location'] = $this->locationManager->getLocationNameByActivityId($id);
        $values['type'] = $node->labels();

        return $values;
    }

    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromEvaluationActivityNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['academicYear'] = $this->academicYearManager->getAcademicYearDetailsByActivityId($id);
        $values['teacher'] = $this->teacherManager->getTeacherByEvaluationActivityId($id);
        $values['subject'] = $this->subjectManager->getSubjectByActivityId($id);
        $values['participants'] = $this->participantManager->getParticipantsByActivityId($id);
        $values['location'] = $this->locationManager->getLocationNameByActivityId($id);

        return $values;
    }

    /**
     * @param string $academicYear
     * @param int $semesterNumber
     * @param string $fileContent
     * @return array
     */
    public function loadTeachingActivitiesFromCsv(string $academicYear, int $semesterNumber, string $fileContent)
    {
        $this->getEntityManager()->clear();
        /** @var array $serializedContent */
        $serializedContent = $this->getSerializedCsv($fileContent);
        /** @var Semester $semester */
        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

        $errors = array();
        $duplicateActivities = array();
        $persistableActivities = array();

        foreach ($serializedContent as $serializedActivity) {
            $activity = null;

            try {
                $activity = $this->getTeachingActivityFor($semester, $serializedActivity);
            } catch (HttpException $exception) {
                $errors[] = $exception->getMessage();
                continue;
            }

            if ($this->activityOverlapsChecker->checkIfTeachingActivityExists($activity)) {
                $duplicateActivities[] = $activity;
                continue;
            }

            $persistableActivities[] = $activity;
        }

        if (!empty($errors)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, json_encode($errors));
        }

        if (!empty($duplicateActivities)) {
            throw new HttpException(Response::HTTP_CONFLICT, $this->getAlreadyExistsMessageFor($duplicateActivities));
        }
        $z = array();

        if (empty($duplicateActivities) && empty($activitiesWithProblems)) {
            /** @var TeachingActivity $activity */
            foreach ($persistableActivities as $activity) {
                $z[] = $activity->jsonSerialize();
            }
        }

        return $z;
        //$this->getEntityManager()->flush();
    }

    /**
     * @param string $academicYearName
     * @param string $evaluationActivityType
     * @param string $fileContent
     */
    public function loadEvaluationActivitiesFromCsv(string $academicYearName, string $evaluationActivityType, string $fileContent)
    {
        $this->getEntityManager()->clear();
        /** @var array $serializedContent */
        $serializedContent = $this->getSerializedCsv($fileContent);
        /** @var Semester $semester */
        $academicYear = $this->academicYearManager->getAcademicYearByName($academicYearName);

        $errors = array();
        $duplicateActivities = array();
        $persistableActivities = array();

        foreach ($serializedContent as $serializedActivity) {
            $activity = null;

            try {
                $activity = $this->getEvaluationActivityFor($academicYear, $evaluationActivityType, $serializedActivity);
            } catch (HttpException $exception) {
                $errors[] = $exception->getMessage();
                continue;
            }
//TODO check for duplicates
//            if ($this->activityOverlapsChecker->checkIfTeachingActivityExists($activity)) {
//                $duplicateActivities[] = $activity;
//                continue;
//            }

            $persistableActivities[] = $activity;
        }

        if (!empty($errors)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, json_encode($errors));
        }

        if (!empty($duplicateActivities)) {
            throw new HttpException(Response::HTTP_CONFLICT, $this->getAlreadyExistsMessageFor($duplicateActivities));
        }

        if (empty($duplicateActivities) && empty($activitiesWithProblems)) {
            /** @var EvaluationActivity $activity */
            foreach ($persistableActivities as $activity) {
                $this->getEntityManager()->persist($activity);
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Semester $semester
     * @param array $serializedActivity
     * @return TeachingActivity
     */
    private function getTeachingActivityFor(Semester $semester, array $serializedActivity)
    {
        /** @var Location $location */
        $locationName = $serializedActivity['sala'];
        $location = $this->locationManager->getLocationByShortName($locationName);
        $this->locationManager->throwNotFoundExceptionOnNullLocationWithName($location, $locationName);
        $location = $location[0];

        /** @var Teacher $teacher */
        $teacherName = $serializedActivity['profesor'];
        $teacher = $this->teacherManager->getTeacherByFullName($teacherName);
        $this->teacherManager->throwNotFoundExceptionOnNullTeacherWithName($teacher, $teacherName);
        $teacher = $teacher[0];

        /** @var Subject $subject */
        $subjectName = $serializedActivity['materie'];
        $subject = $this->subjectManager->getSubjectByShortName($subjectName);
        $this->subjectManager->throwNotFoundExceptionOnNullSubjectWithName($subject, $subjectName);
        $subject = $subject[0];

        $activityCategory = $this->activityInternationalDate->getActivityTypeFromRo($serializedActivity['tip_activitate']);
        $weekType = $this->activityInternationalDate->getWeekTypeFromRo($serializedActivity['repeta']);
        $day = $serializedActivity['zi'];
        $hour = $serializedActivity['ora'];
        $duration = $serializedActivity['durata'] == '' ? 2 : (int)$serializedActivity['durata'];

        $participants = $this->participantManager->deserializeParticipants($serializedActivity['participanti']);

        $activity = new TeachingActivity($location, $activityCategory, $semester, $weekType, (int)$day, (int)$hour, (int)$duration, $teacher, $subject, $participants);
        $activity->setParticipants($participants);

        return $activity;
    }

    /**
     * @param AcademicYear $academicYear
     * @param $evaluationActivityType
     * @param array $serializedActivity
     * @return EvaluationActivity
     */
    private function getEvaluationActivityFor(AcademicYear $academicYear, $evaluationActivityType, array $serializedActivity)
    {
        /** @var Location $location */
        $locationName = $serializedActivity['sala'];
        $location = $this->locationManager->getLocationByShortName($locationName);
        $this->locationManager->throwNotFoundExceptionOnNullLocationWithName($location, $locationName);
        $location = $location[0];

        /** @var Teacher $teacher */
        $teacherName = $serializedActivity['profesor'];
        $teacher = $this->teacherManager->getTeacherByFullName($teacherName);
        $this->teacherManager->throwNotFoundExceptionOnNullTeacherWithName($teacher, $teacherName);
        $teacher = $teacher[0];

        /** @var Subject $subject */
        $subjectName = $serializedActivity['materie'];
        $subject = $this->subjectManager->getSubjectByShortName($subjectName);
        $this->subjectManager->throwNotFoundExceptionOnNullSubjectWithName($subject, $subjectName);
        $subject = $subject[0];

        $activityCategory = $this->activityInternationalDate->getActivityTypeFromRo($serializedActivity['tip-evaluare']);

        $date = new \DateTime($serializedActivity['data']);
        $hour = $serializedActivity['ora'];
        $duration = $serializedActivity['durata'] == '' ? 2 : (int)$serializedActivity['durata'];

        $participants = $this->participantManager->deserializeParticipants($serializedActivity['participanti']);

        $activity = new EvaluationActivity(
            $location,
            $activityCategory,
            $evaluationActivityType,
            $hour,
            $date,
            $duration,
            $subject,
            $teacher,
            $academicYear
        );

        $activity->setParticipants($participants);


        return $activity;
    }


    /**
     * @param string $fileContent
     * @return mixed
     */
    private function getSerializedCsv(string $fileContent)
    {
        return $this->serializer->decode($fileContent, 'csv');
    }

    /**
     * @param $activity
     */
    public function throwNotFoundExceptionOnNull($activity)
    {
        if ($activity === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'No activity');
        }
    }

    /**
     * @param TeachingActivity[] $activities
     * @return string
     */
    private function getAlreadyExistsMessageFor($activities)
    {
        $message = array();
        foreach ($activities as $activity) {
            $message[] = $activity->jsonSerialize();
        }

        return $this->serializer->serialize($message, 'json');
    }
}