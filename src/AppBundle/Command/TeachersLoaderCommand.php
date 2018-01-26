<?php


namespace AppBundle\Command;


use AppBundle\Service\TeacherManagerService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TeachersLoaderCommand
 * @package AppBundle\Command
 */
class TeachersLoaderCommand extends BaseLoaderCommand
{
    protected function configure()
    {
        $this->setName('app:teachers_loader')
            ->addArgument('input_file_path', InputArgument::REQUIRED, 'The csv file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Teachers sync',
            '============'
        ]);

        $inputPath = $input->getArgument('input_file_path');

        /** @var array $teachers */
        $teachers = array_column($this->getCsvData($inputPath), 'teachers');

        /** @var TeacherManagerService $teacherManagerService */
        $teacherManagerService = $this->getContainer()->get(TeacherManagerService::SERVICE_NAME);

        foreach ($teachers as $teacherFullName) {

            $teacherFullName = trim($teacherFullName);

            $output->writeln('Load "' . $teacherFullName . '"');


            $teacher = $teacherManagerService->getTeacherByFullName($teacherFullName);

            if ($teacher === null) {
                $parts = explode(' ', $teacherFullName);

                $firstName = implode(' ', array_slice($parts, 0, -1));
                $lastName = end($parts);
                $email = strtolower(implode('_', $parts) . '@itt.com');

                try {
                    $teacherManagerService->createNew($firstName, $lastName, $email);
                }catch (\Exception $exception){
                    continue;
                }
            }
        }

        $output->writeln("Import finished");
    }
}