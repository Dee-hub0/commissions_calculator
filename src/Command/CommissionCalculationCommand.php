<?php

namespace App\Command;

use App\Service\Commission\CommissionCalculationHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

#[AsCommand(name: 'app:calculate-commissions')]
class CommissionCalculationCommand extends Command
{
    use LockableTrait;

    private CommissionCalculationHandler $commissionCalcHandler;

    public function __construct(CommissionCalculationHandler $commissionCalcHandler)
    {
        parent::__construct();

        $this->commissionCalcHandler = $commissionCalcHandler;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Processes a CSV file and performs commissions calculation.')
            ->addArgument('csv-file', InputArgument::OPTIONAL, 'Path to the CSV file to process', './public/testData/operations.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new SymfonyStyle($input, $output);

        // if(!$this->lock()){
        //     $output->error("This command is already running in another process.");
        //     return Command::SUCCESS;
        // }

        $csvFilePath = $input->getArgument('csv-file');
        
        if (!$this->isValidCsvFile($csvFilePath)) {
            $output->error("CSV file not found at path: $csvFilePath");
            return Command::FAILURE;
        }
        try {
            $operations = $this->commissionCalcHandler->processCsv($csvFilePath);
            if (empty($operations)) {
                $output->success('No operations found in the CSV file.');
                return Command::SUCCESS;
            }
            foreach ($operations as $operation) {
                $output->writeln($operation['commission']);
            }
            $output->success('Commission calculations completed successfully.');

        } catch (\Exception $exception) {
            $output->error('An error occurred during commission calculation: ' . $exception->getMessage());
            return Command::FAILURE;
        }

        $this->release();

        return Command::SUCCESS;
    }

    private function isValidCsvFile(string $filePath): bool
    {
        return file_exists($filePath) && is_readable($filePath);
    }
}