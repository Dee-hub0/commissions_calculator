<?php

namespace App\Tests\Command;

use App\Command\CommissionCalculationCommand;
use App\Service\Commission\CommissionCalculationHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CommissionCalculationCommandTest extends TestCase
{
    private $commissionCalcHandlerMock;
    private $command;

    protected function setUp(): void
    {
        $this->commissionCalcHandlerMock = $this->createMock(CommissionCalculationHandler::class);
        $this->command = new CommissionCalculationCommand($this->commissionCalcHandlerMock);
    }

    /**
     * Simulate successful CSV processing
     */
    public function testCommandSuccessWithValidCsv()
    {
        $this->commissionCalcHandlerMock
            ->expects($this->once())
            ->method('processCsv')
            ->with('./public/testData/operations.csv')
            ->willReturn([
                ['commission' => 100],
                ['commission' => 200]
            ]);

        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['csv-file' => './public/testData/operations.csv']);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Commission calculations completed successfully.', $output);
        $this->assertStringContainsString('100', $output);
        $this->assertStringContainsString('200', $output);
    }

    /**
     * Simulate invalid file scenario
     */
    public function testCommandFailureWhenFileNotFound()
    {
        $this->commissionCalcHandlerMock
            ->expects($this->never())
            ->method('processCsv');

        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['csv-file' => './invalid/path.csv']);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('CSV file not found at path: ./invalid/path.csv', $output);
    }

    /**
     * Simulate an exception in the CSV processing
     */
    public function testCommandFailureOnException()
    {
        $this->commissionCalcHandlerMock
            ->expects($this->once())
            ->method('processCsv')
            ->willThrowException(new \Exception('Error during commission calculation'));

        $application = new Application();
        $application->add($this->command);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['csv-file' => './public/testData/operations.csv']);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('An error occurred during commission calculation: Error during commission calculation', $output);
    }
}