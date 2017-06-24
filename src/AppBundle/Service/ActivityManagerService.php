<?php


namespace AppBundle\Service;

use AppBundle\Model\NodeEntity\EvaluationActivity;
use AppBundle\Model\NodeEntity\Location;
use AppBundle\Model\NodeEntity\Participant;
use AppBundle\Model\NodeEntity\Semester;
use AppBundle\Model\NodeEntity\Subject;
use AppBundle\Model\NodeEntity\Teacher;
use AppBundle\Model\NodeEntity\TeachingActivity;
use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Model\NodeEntity\Util\ParticipantType;
use AppBundle\Model\NodeEntity\Util\WeekType;
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
     * @param Serializer $serializer
     */
    public function __construct(
        LocationManagerService $locationManager, AcademicYearManagerService $academicYearManager,
        SubjectManagerService $subjectManager, TeacherManagerService $teacherManager,
        SeriesManagerService $seriesManager, SpecializationManagerService $specializationManager,
        AcademicYearService $yearService,
        ParticipantManagerService $participantManager,
        StudentManagerService $studentManagerService,
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
        $this->serializer = $serializer;
    }

    public function getActivityDetailsById(int $id)
    {
        $result = $this->getEntityManager()
            ->createQuery(' MATCH (a:Activity) where ID(a) = {actId} return a;')
            ->setParameter('actId', $id)
            ->getOneOrNullResult();

        $this->throwNotFoundExceptionOnNull($result);

        return $this->getPropertiesFromNode($result[0]['a']);
    }

    /**
     * @param int $id
     * @return TeachingActivity
     */
    public function getActivityById(int $id)
    {
        /** @var TeachingActivity $result */
        $result = $this->getEntityManager()
            ->getRepository(TeachingActivity::class)
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
        $activity = $this->getActivityById($activityId);

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
            if($changes['weekType']!= $activity->getWeekType()) {
                $activity->setWeekType($changes['weekType']);
            }
        }
        if (!is_null($changes['duration'])) {
            if($changes['duration']!= $activity->getDuration()) {
                $activity->setDuration($changes['duration']);
            }
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


    /**
     * @param Location $location
     * @param string $activityCategory
     * @param \DateTime $date
     * @param int $duration
     * @param Subject $subject
     */
    private function createAndPersistEvaluationActivity(
        Location $location,
        string $activityCategory,
        \DateTime $date,
        int $duration,
        Subject $subject
    )
    {
        $activity = new EvaluationActivity($location, $activityCategory, $date, $duration, $subject);

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
            $activities[] = $this->getPropertiesFromNode($act['a']);
        }

        return $activities;
    }


    /**
     * @param int $specializationId
     * @param int $yearOfStudy
     * @param \DateTime $date
     * @return array
     */
    public function getActivitiesForParticipantOnDate(int $specializationId, int $yearOfStudy, \DateTime $date)
    {
        $specialization = $this->specializationManager->getSpecializationById($specializationId);

        $data = array();

        try {
            $data = $this->academicYearService->getActivityDetailsOnDate($date, $yearOfStudy, $specialization->getSpecializationCategory());
        } catch (RequestException $exception) {
            if ($exception->getResponse()->getStatusCode() == Response::HTTP_NOT_FOUND) {
                return array();
            }
            throw $exception;
        }

        $weekNumber = $data['weekNumber'];
        $correspondingActivity = $data['activity']['activityType'];

        $semesterDetails = $data['activity']['activityGroup']['semester'];
        $academicYear = $semesterDetails['academicYear']['years'];
        $semesterNumber = $semesterDetails['number'];

        $weekType = $weekNumber % 2 == 0 ? '\'even\'' : '\'odd\'';

        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);
        $result = array();

        switch ($correspondingActivity) {
            case 'PREDARE':
                $dayNumber = (int)date('w', $date->getTimestamp());
                $result = $this->getEntityManager()
                    ->createQuery(' MATCH (spec:Participant)<-[r:PART_OF*]-(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_SEMESTER]->(sem:Semester) where ID(sem) = {semId} AND ID(spec) = {specId}   AND a.day = {dayNum}  AND a.weekType = \'every\' OR  a.weekType = {weekT}  return a;')
                    ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                    ->setParameter('semId', $semester->getId())
                    ->setParameter('specId', $specialization->getId())
                    ->setParameter('dayNum', $dayNumber)
                    ->setParameter('weekT', $weekType)
                    ->getResult();
                break;
            default:
                throw new HttpException(Response::HTTP_NOT_FOUND, 'No teaching activities were founded on this day');
        }

        $activities = array();
        foreach ($result as $act) {
            $activities[] = $this->getPropertiesFromNode($act['a']);
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


        $data = array();

        try {
            $data = $this->academicYearService->getActivityDetailsOnDate(
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

        $weekNumber = $data['weekNumber'];
        $correspondingActivity = $data['activity']['activityType'];

        $semesterDetails = $data['activity']['activityGroup']['semester'];
        $academicYear = $semesterDetails['academicYear']['years'];
        $semesterNumber = $semesterDetails['number'];

        $weekType = $weekNumber % 2 == 0 ? 'even' : 'odd';


        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);

        $result = array();

        switch ($correspondingActivity) {
            case 'PREDARE':
//                $dayNumber = (int) date('w', $date->getTimestamp());
                $result = $this->getEntityManager()
                    ->createQuery('MATCH (spec:Participant)-[r:PART_OF*0..]->(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_SEMESTER]->(sem:Semester) where ID(sem) = {semId} AND ID(spec) = {specId} AND (a.weekType = \'every\' OR  a.weekType = {weekT})  return a;')
                    ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                    ->setParameter('semId', $semester->getId())
                    ->setParameter('specId', $student->getSubSeries()->getId())
//                    ->setParameter('dayNum', $dayNumber)
                    ->setParameter('weekT', $weekType)
                    ->getResult();
                break;
            case 'EXAMINARE':
                return array();
                break;

            case 'PRACTICA':

                return array(
                    array(
                        'activityCategory' => 'practice',
                        "activityName" => $data['activity']['activityName'],
                        'period' => $data['activity']['period']
                    )
                );
                break;

            case 'LIBER':
                return array();
                break;
            default:
                throw new HttpException(Response::HTTP_NOT_FOUND, 'No activities were founded on this day');
        }

        $activities = array();
        foreach ($result as $act) {
            $activities[] = $this->getPropertiesFromNode($act['a']);
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
        $student = $this->studentManager->getStudentById($teacherId);

        $data = array();

        try {
            $data = $this->academicYearService->getActivityDetailsOnDate(
                $date,
                $student->getSubSeries()->getSeries()->getYearOfStudy(),
                $student->getSubSeries()->getSeries()->getSpecialization()->getSpecializationCategory()
            );
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() != Response::HTTP_NOT_FOUND) {
                throw $exception;
            }
        }

        $weekNumber = $data['weekNumber'];
        $correspondingActivity = $data['activity']['activityType'];

        $semesterDetails = $data['activity']['activityGroup']['semester'];
        $academicYear = $semesterDetails['academicYear']['years'];
        $semesterNumber = $semesterDetails['number'];

        $weekType = $weekNumber % 2 == 0 ? '\'even\'' : '\'odd\'';

        $semester = $this->academicYearManager->getSemesterByAcademicYearNameAndNumber($academicYear, $semesterNumber);
        $result = array();

        switch ($correspondingActivity) {
            case 'PREDARE':
                $dayNumber = (int)date('w', $date->getTimestamp());
                $result = $this->getEntityManager()
                    ->createQuery(' MATCH (spec:Participant)<-[r:PART_OF*]-(p:Participant)-[:PARTICIPATE]->(a:Activity)-[:ON_SEMESTER]->(sem:Semester) where ID(sem) = {semId} AND ID(spec) = {specId}   AND a.day = {dayNum}  AND a.weekType = \'every\' OR  a.weekType = {weekT}  return a;')
                    ->addEntityMapping('a', TeachingActivity::class, Query::HYDRATE_RAW)
                    ->setParameter('semId', $semester->getId())
                    ->setParameter('specId', $student->getSubSeries()->getId())
                    ->setParameter('dayNum', $dayNumber)
                    ->setParameter('weekT', $weekType)
                    ->getResult();
                break;
            default:
                throw new HttpException(Response::HTTP_NOT_FOUND, 'No teaching activities were founded on this day');
        }

        $activities = array();
        foreach ($result as $act) {
            $activities[] = $this->getPropertiesFromNode($act['a']);
        }

        return $activities;
    }

    /**
     * @param Node $node
     * @return array
     */
    private function getPropertiesFromNode($node)
    {
        $id = $node->identity();
        $values = $node->values();
        $values['id'] = $id;
        $values['semester'] = $this->academicYearManager->getSemesterByActivityId($id);
        $values['teacher'] = $this->teacherManager->getTeacherByActivityId($id);
        $values['subject'] = $this->subjectManager->getSubjectByActivityId($id);
        $values['participants'] = $this->participantManager->getParticipantsByActivityId($id);
        $values['location'] = $this->locationManager->getLocationNameByActivityId($id);
        $values['type'] = $node->labels();

        return $values;
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
     * @param $type
     * @param $identifier
     * @return Participant
     */
    private function getParticipantByTypeAndIdentifier($type, $identifier)
    {
        if (!ParticipantType::isValidValue(strtolower($type))) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid participant type:' . $type);
        }
        //TODO add specialization

        $participant = null;

        switch ($type) {
            case ParticipantType::SERIES:
                $participant = $this->seriesManager->getSeriesByIdentifier($identifier)[0];
                break;
            case ParticipantType::SUB_SERIES:
                $participant = $this->seriesManager->getSubSeriesByIdentifier($identifier)[0];
                break;
        }

        return $participant;
    }

    /**
     * @param string $academicYear
     * @param int $semesterNumber
     * @param string $fileContent
     */
    public function loadActivitiesFromCsv(string $academicYear, int $semesterNumber, string $fileContent)
    {
        $this->getEntityManager()->clear();

        /** @var array $serializedContent */
        $serializedContent = $this->getSerializedCsv($fileContent);

        $academicYear = $this->academicYearManager->getAcademicYearByName($academicYear);
        $semester = $this->academicYearManager->getSemesterByAcademicYearAndNumber($academicYear, $semesterNumber);

        foreach ($serializedContent as $serializedActivity) {
            $activity = $this->getActivityFor($semester, $serializedActivity);
            $this->getEntityManager()->persist($activity);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param Semester $semester
     * @param array $serializedActivity
     * @return TeachingActivity
     */
    private function getActivityFor(Semester $semester, array $serializedActivity)
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

        $activityCategory = $this->getActivityTypeFromRo($serializedActivity['tip_activitate']);
        $weekType = $this->getWeekTypeFromRo($serializedActivity['repeta']);
        $day = $serializedActivity['zi'];
        $hour = $serializedActivity['ora'];
        $duration = $serializedActivity['durata'] == '' ? 2 : (int)$serializedActivity['durata'];

        $participants = $this->deserializeParticipants($serializedActivity['participanti']);

        return new TeachingActivity($location, $activityCategory, $semester, $weekType, (int)$day, (int)$hour, (int)$duration, $teacher, $subject, $participants);
    }

    /**
     * @param string $serializedParticipants
     * @return Collection
     */
    private function deserializeParticipants(string $serializedParticipants)
    {
        $splited = explode('|', $serializedParticipants);


        $participants = new Collection();

        foreach ($splited as $serializedParticipant) {
            $x = explode(':', $serializedParticipant);

            $type = $this->getParticipantTypeFromRo(trim($x[0]));
            $identifier = trim($x[1]);

            $participants->add($this->getParticipantByTypeAndIdentifier($type, $identifier));
        }

        return $participants;
    }

    /**
     * @param $type
     * @return null|string
     */
    private function getParticipantTypeFromRo($type)
    {
        switch ($type) {
            case 'grupa':
                return ParticipantType::SERIES;
            case 'subgrupa':
                return ParticipantType::SUB_SERIES;
            case 'specializare':
                return ParticipantType::SPECIALIZATION;
            default:
                return null;
        }
    }

    /**
     * @param $type
     * @return null|string
     */
    private function getWeekTypeFromRo($type)
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
    private function getActivityTypeFromRo($type)
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

    /**
     * @param string $fileContent
     * @return mixed
     */
    private function getSerializedCsv(string $fileContent)
    {
        return $this->serializer->decode($fileContent, 'csv');
    }

    public function throwNotFoundExceptionOnNull($activity)
    {
        if ($activity === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'No activity');
        }
    }


    /**
     * @param int $activityId
     */
    private function removeOldLocationRelation($activityId){
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:IN]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldSubjectRelation($activityId){
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:LINKED_TO]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldTeacherRelation($activityId){
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:TEACHED_BY]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldSemesterRelation($activityId){
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:ON_SEMESTER]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }

    /**
     * @param int $activityId
     */
    private function removeOldParticipantsRelation($activityId){
        $this->getEntityManager()
            ->createQuery('MATCH (a:Activity)-[r:PARTICIPATE]-() WHERE id(a)= {actId} DELETE r')
            ->setParameter('actId', $activityId)
            ->execute();
    }
}