<?php


namespace AppBundle\Command;


use AppBundle\Model\NodeEntity\Location;
use AppBundle\Service\LocationManagerService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RoomsLoaderCommand
 * @package AppBundle\Command
 */
class RoomsLoaderCommand extends BaseLoaderCommand
{
    protected function configure()
    {
        $this->setName('app:rooms_loader')
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
            'Rooms sync',
            '============'
        ]);

        $inputPath = $input->getArgument('input_file_path');

        /** @var array $rooms */
        $rooms = array_column($this->getCsvData($inputPath), 'rooms');

        /** @var LocationManagerService $locationManagerService */
        $locationManagerService = $this->getContainer()->get(LocationManagerService::SERVICE_NAME);

        foreach ($rooms as $room) {

            if($room == null){
                continue;
            }

            $room = trim($room);

            try {
                $output->writeln(sprintf('Load room %s', $room));
                $locationManagerService->createNew($room, $room);
            }catch (\Exception $exception){
                continue;
            }
        }

        $output->writeln("Import finished");
    }
}