<?php


namespace AppBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ParticipantAllocatorCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:participant-alocator')
            ->addArgument('input_file_path', InputArgument::REQUIRED, 'The csv file')
            ->addArgument('output_file_path', InputArgument::REQUIRED, 'The csv file')
            ->setDescription('Alocate participands based on timetable data');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Tranforming file',
            '============',
            '',
        ]);

        $inputPath = $input->getArgument('input_file_path');
        $outputPath = $input->getArgument('output_file_path');


        $output->writeln('Input path: ' . $inputPath . ' Output path: ' . $outputPath);

        $this->handleTransformation($inputPath, $outputPath);

        $output->writeln('File transformed');
    }

    /**
     * @param string $inputPath
     * @param string $outputPath
     */
    private function handleTransformation(string $inputPath, string $outputPath)
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(',')]);

        $fileContent = $this->getFileContent($inputPath);

        $data = $serializer->decode(
            $fileContent,
            'csv'
        );

        $groupedData = $this->groupArrayByKey($data, 'Sectie');
        $groupedData = $this->groupArrayByKey($groupedData, 'An', 2);
        $groupedData = $this->groupArrayByKey($groupedData, 'Disciplina', 3);

        foreach ($groupedData as $section => $years) {
            foreach ($years as $year => $subjectGroup) {

                $numberOfGroups = 1;
                $numberOfSubgroups = 1;


                foreach ($subjectGroup as $subject => $details) {

                    $seminaries = array_filter($details, function ($activity) {
                        return $activity['Seminar'] != '';
                    });
                    $laboratories = array_filter($details, function ($activity) {
                        return $activity['Laborator'] != '';
                    });
                    $projects = array_filter($details, function ($activity) {
                        return $activity['Proiect'] != '';
                    });

                    $tmpNumberOfGroups = count($seminaries) == 0 ? count($projects) : count($seminaries);
                    $tmpNumberOfSubgroups = count($laboratories) == 0 ? 2 * $tmpNumberOfGroups : count($laboratories);
                    $tmpNumberOfSubgroups = $tmpNumberOfSubgroups == 0 ? 2 * $tmpNumberOfGroups : $tmpNumberOfSubgroups;


                    if ($tmpNumberOfGroups > $numberOfGroups) {
                        $numberOfGroups = $tmpNumberOfGroups;
                    }
                    if ($tmpNumberOfSubgroups > $numberOfSubgroups) {
                        $numberOfSubgroups = $tmpNumberOfSubgroups;
                    }
                }


                dump($numberOfSubgroups . '-'. $numberOfGroups . '- y: '. $year . ' s: '.$section);

                foreach ($subjectGroup as $subject => $details) {

                    $courses = array_filter($details, function ($activity) {
                        return $activity['Curs'] != '';
                    });
                    $seminaries = array_filter($details, function ($activity) {
                        return $activity['Seminar'] != '';
                    });
                    $laboratories = array_filter($details, function ($activity) {
                        return $activity['Laborator'] != '';
                    });
                    $projects = array_filter($details, function ($activity) {
                        return $activity['Proiect'] != '';
                    });


                    //dump($section . '.' . $year . '-' . $numberOfSubgroups);
                    foreach ($courses as $key => $activity) {
                        $data[$key]['participant'] = 'specializare: ' . $section;
                    }

                    $seminarIndex = 1;
                    foreach ($seminaries as $key => $activity) {
                        $data[$key]['participant'] = sprintf('grupa: %s%s.%s', $section, $year, $seminarIndex);
                        $seminarIndex++;
                    }

                    $projectIndex = 1;
                    foreach ($projects as $key => $activity) {
                        $participant = sprintf('grupa: %s%s.%s', $section, $year, $projectIndex);
                        $data[$key]['participant'] = $participant;
                        $projectIndex++;
                    }

                    $group = 1;
                    $sgNumber = 'A';
                    $labIndex = 1;
                    foreach ($laboratories as $key => $activity) {

                        if ($labIndex > round($numberOfSubgroups / $numberOfGroups)) {
                            $group++;
                            $labIndex = 1;
                            $sgNumber = 'A';
                        }

                        $participant = sprintf('subgrupa: %s%s.%s-%s', $section, $year, $group, $sgNumber);
                        $data[$key]['participant'] = $participant;
                        $labIndex++;
                        $sgNumber++;
                    }
                }
            }
        }


        $result = $data;


        file_put_contents(
            $outputPath,
            $serializer->encode($result, 'csv')
        );
    }


    /**
     * @param array $data
     * @param string $groupKey
     * @param int $level
     * @return array
     */
    private function groupArrayByKey(array $data, string $groupKey, int $level = 1)
    {
        $groupedData = array();
        $tmpLevel = $level;

        if ($level <= 1) {
            foreach ($data as $key => $value) {
                $c = $value[$groupKey];

                if ($c == '') {
                    continue;
                }

                $groupedData[$c][$key] = $value;
            }

            return $groupedData;
        }


        $tmpLevel--;
        foreach ($data as $key => $value) {
            $groupedData[$key] = $this->groupArrayByKey($value, $groupKey, $tmpLevel);
        }

        return $groupedData;
    }

    /**
     * @param $filePath
     * @return string
     */
    function getFileContent($filePath)
    {
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $filePath));
        }


        $content = file_get_contents($filePath);
        return mb_convert_encoding($content, 'UTF-8',
            mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }
}