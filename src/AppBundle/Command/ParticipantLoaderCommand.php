<?php


namespace AppBundle\Command;


use AppBundle\Model\NodeEntity\Util\ParticipantType;
use AppBundle\Service\DepartmentManagerService;
use AppBundle\Service\ParticipantManagerService;
use AppBundle\Service\SeriesManagerService;
use AppBundle\Service\SpecializationManagerService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ParticipantLoaderCommand
 * @package AppBundle\Command
 */
class ParticipantLoaderCommand extends BaseLoaderCommand
{
    protected function configure()
    {
        $this->setName('app:participant_loader')
            ->addArgument('department', InputArgument::REQUIRED, 'Department')
            ->addArgument('category', InputArgument::REQUIRED, 'Category (licenta/master)')
            ->addArgument('input_file_path', InputArgument::REQUIRED, 'The csv file');
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
            'Participant sync',
            '============'
        ]);

        $inputPath = $input->getArgument('input_file_path');
        $departmentName = $input->getArgument('department');
        $category = $input->getArgument('category');

        /** @var array $participants */
        $participants = array_column($this->getCsvData($inputPath), 'participants');

        /** @var ParticipantManagerService $participantManagerService */
        $participantManagerService = $this->getContainer()->get(ParticipantManagerService::SERVICE_NAME);
        /** @var SpecializationManagerService $specializationManager */
        $specializationManager = $this->getContainer()->get(SpecializationManagerService::SERVICE_NAME);
        /** @var DepartmentManagerService $departmentManager */
        $departmentManager = $this->getContainer()->get(DepartmentManagerService::SERVICE_NAME);
        /** @var SeriesManagerService $seriesManager */
        $seriesManager = $this->getContainer()->get(SeriesManagerService::SERVICE_NAME);


        $currentDepartment = $departmentManager->getDepartmentByName($departmentName);

        $participantList = array();
        foreach ($participants as $serializedParticipant) {
            $participant = null;
            try {
                $participant = $participantManagerService->deserializeParticipant($serializedParticipant);
            } catch (\Exception $exception) {
            }

            if ($participant == null) {
                $x = explode(':', $serializedParticipant);

                $type = $participantManagerService->getParticipantTypeFromRo(trim($x[0]));
                $identifier = trim($x[1]);


                if (!ParticipantType::isValidValue(strtolower($type))) {
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid participant type:' . $type);
                }
                $participant = null;

                switch ($type) {
                    case ParticipantType::SERIES:
                        $participant = $seriesManager->defineSeriesByIdentifier($identifier);
                        break;
                    case ParticipantType::SUB_SERIES:
                        $participant = $seriesManager->defineSubSeriesByIdentifier($identifier);
                        break;
                    case ParticipantType::SPECIALIZATION:
                        $participant = $specializationManager->defineSpecializationByIdentifier($identifier, $category, $currentDepartment);
                        break;
                }

            }

            $participantManagerService->createParticipant($participant);
            $participantList[] = $participant;

        }

        $output->writeln("Import finished");
    }
}