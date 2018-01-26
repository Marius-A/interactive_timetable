<?php


namespace AppBundle\Command;

use AppBundle\Model\NodeEntity\Util\ActivityCategory;
use AppBundle\Model\NodeEntity\Util\DayOfWeek;
use AppBundle\Model\NodeEntity\Util\WeekType;
use AppBundle\Service\AcademicYearManagerService;
use AppBundle\Service\ActivityManagerService;
use AppBundle\Service\LocationManagerService;
use AppBundle\Service\ParticipantManagerService;
use AppBundle\Service\SubjectManagerService;
use AppBundle\Service\TeacherManagerService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ActivityImporterCommand
 * @package AppBundle\Command
 */
class ActivityImporterCommand extends BaseLoaderCommand
{
    protected function configure()
    {
        $this->setName('app:activity-importer')
            ->addArgument('academic_year', InputArgument::REQUIRED, '')
            ->addArgument('semester', InputArgument::REQUIRED, '')
            ->addArgument('input_file_path', InputArgument::REQUIRED, 'The csv file')
            ->addOption('offset', 'O', InputOption::VALUE_REQUIRED)
            ->setDescription('Register activities');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Activities sync',
            '============'
        ]);

        $inputPath = $input->getArgument('input_file_path');
        $semesterNumber = $input->getArgument('semester');
        $academicYear = $input->getArgument('academic_year');

        $offset = $input->getOption('offset');

        $offset = $offset == null ? 0 : $offset;

        $output->writeln('Input path: ' . $inputPath);
        $output->writeln(' Academic year: ' . $academicYear);
        $output->writeln(' Semester ' . $semesterNumber);

        $fileContent = $this->getCsvData($inputPath);

        $this->registerActivities($academicYear, $semesterNumber, $fileContent, $offset);

        $output->writeln('File transformed');
    }

    /**
     * @param string $academicYear
     * @param int $semester
     * @param array $data
     * @throws \Exception
     */
    public function registerActivities(string $academicYear, int $semester, array $data, int $offset)
    {

        /** @var AcademicYearManagerService $academicYearManager */
        $academicYearManager = $this->getContainer()->get(AcademicYearManagerService::SERVICE_NAME);
        $participantManager = $this->getContainer()->get(ParticipantManagerService::SERVICE_NAME);
        /** @var ActivityManagerService $activityManager */
        $activityManager = $this->getContainer()->get(ActivityManagerService::SERVICE_NAME);
        /** @var TeacherManagerService $teacherManager */
        $teacherManager = $this->getContainer()->get(TeacherManagerService::SERVICE_NAME);
        /** @var SubjectManagerService $teacherManager */
        $subjectManager = $this->getContainer()->get(SubjectManagerService::SERVICE_NAME);
        /** @var LocationManagerService $locationManager */
        $locationManager = $this->getContainer()->get(LocationManagerService::SERVICE_NAME);

        foreach ($data as $index => $serializedActivity) {

            if ($index <= $offset) {
                continue;
            }

            $smDetails = $this->getActivityCategory($serializedActivity);

            if (empty($smDetails)) {
                throw new \Exception('Invalid activity ' . json_encode($serializedActivity));
            }


            $weekType = trim($serializedActivity['Saptamana']);
            $weekType = $weekType === '' ? 'every' : ($weekType === 'P' ? WeekType::EVEN : WeekType::ODD);

            dump(trim($serializedActivity['Saptamana']), $weekType);
            $teacher = $teacherManager->getTeacherByFullName($serializedActivity['Cadru Didactic'])[0];
            $subject = $subjectManager->getSubjectByShortName($serializedActivity['Disciplina'])[0];
            $location = $locationManager->getLocationByFullName($serializedActivity['Sala'])[0];
            $participant = $participantManager->deserializeParticipant($serializedActivity['participant']);

            $hour = $serializedActivity['Ora '] ?? null;

            dump('index: ' . $index);

            $activityManager->createTeachingActivity(
                $smDetails['type'],
                $academicYear,
                $semester,
                $weekType,
                DayOfWeek::getConstantValue($serializedActivity['Zi']),
                $hour,
                $smDetails['duration'],
                $teacher->getId(),
                $subject->getId(),
                $location->getId(),
                array(
                    array(
                        'id' => $participant->getId(),
                        'type' => $participant->getType()
                    )
                )
            );

            continue;
        }
    }

    /**
     * @param array $activity
     * @return array
     */
    private function getActivityCategory(array $activity)
    {
        if ($activity['Curs']) {
            return array(
                'type' => ActivityCategory::COURSE,
                'duration' => $activity['Curs']

            );
        }
        if ($activity['Laborator']) {
            return array(
                'type' => ActivityCategory::LABORATORY,
                'duration' => $activity['Laborator']

            );
        }
        if ($activity['Curs']) {
            return array(
                'type' => ActivityCategory::COURSE,
                'duration' => $activity['Curs']

            );
        }
        if ($activity['Seminar']) {
            return array(
                'type' => ActivityCategory::SEMINAR,
                'duration' => $activity['Seminar']

            );
        }

        if ($activity['Proiect']) {
            return array(
                'type' => ActivityCategory::PROJECT,
                'duration' => $activity['Proiect']

            );
        }

        return array();
    }

}