<?php


namespace AppBundle\Command;


use AppBundle\Service\SubjectManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class SubjectsLoaderCommand
 * @package AppBundle\Command
 */
class SubjectsLoaderCommand extends BaseLoaderCommand
{
    protected function configure()
    {
        $this->setName('app:subject_loader')
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
            'Activities sync',
            '============'
        ]);

        $inputPath = $input->getArgument('input_file_path');

        /** @var array $subjectList */
        $subjectList = array_column($this->getCsvData($inputPath), 'subjects');

        /** @var SubjectManagerService $subjectManager */
        $subjectManager = $this->getContainer()->get(SubjectManagerService::SERVICE_NAME);

        foreach ($subjectList as $subjectName){

            $output->writeln('Load "' . $subjectName . '"');

            $subject = $subjectManager->getSubjectByShortName($subjectName);

            if($subject === null){
                $subjectManager->createNew($subjectName,$subjectName, '-');
            }
        }

        $output->writeln("Import finished");
    }
}