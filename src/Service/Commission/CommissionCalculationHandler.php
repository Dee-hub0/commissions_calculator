<?php

namespace App\Service\Commission;

use App\Dto\OperationDto;
use App\Factory\OperationFactory;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Validation;
use Psr\Log\LoggerInterface;

class CommissionCalculationHandler
{
    private OperationFactory $operationFactory;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(OperationFactory $operationFactory,
        ValidatorInterface $validator, 
        LoggerInterface $logger)
    {
        $this->operationFactory = $operationFactory;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function calculateCommission(OperationDto $operationDto): float
    {
        $operationType = $operationDto->getOperationType();
        $userType = $operationDto->getUserType();
        $operationStrategy = $this->operationFactory->create($operationType, $userType);

        return $operationStrategy->calculate($operationDto);
    }

    public function processCsv($csvFile): array
    {
        $csvFile = $this->validateAndConvertFile($csvFile);
        $operations = [];
        $handle = fopen($csvFile->getRealPath(), 'r');
        if ($handle === false) {
            throw new \Exception("Failed to open the CSV file.");
        }
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 6) {
                try {
                    $operationDto = $this->createOperationDto($data);
                    $violations = $this->validator->validate($operationDto);
                    if (count($violations) > 0) {
                        continue;
                    }
                    $calculatedComission = $this->calculateCommission($operationDto);
                    $commission = $this->roundToTwoDecimals($calculatedComission);

                    $operations[] = ['operation' => $operationDto, 'commission' => $commission];
                    
                } catch (\Exception $e) {
                    $this->logger->error('An error occurred while processing the operation.', [
                        'exception' => $e,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    continue;
                }
            }
        }

        fclose($handle);

        return $operations;
    }

    private function validateAndConvertFile($csvFile): File
    {
        if (is_string($csvFile)) {
            $csvFile = new File($csvFile);
        }

        if (!$csvFile instanceof File) {
            throw new InvalidArgumentException("Invalid file provided.");
        }

        return $csvFile;
    }

    private function createOperationDto(array $data): OperationDto
    {
        $operationDate = $this->parseDate($data[0]);
        if (!$operationDate) {
            throw new \InvalidArgumentException("Invalid operation date format.");
        }

        return new OperationDto(
            $operationDate,
            (int)$data[1],
            $data[2],
            $data[3],
            (float)$data[4],
            $data[5]
        );
    }

    private function parseDate(string $dateString): ?\DateTime
    {
        $formats = ['Y-m-d', 'm/d/Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date;
            }
        }
        return null;
    }

    private function roundToTwoDecimals(float $amount): string
    {
        return number_format((float)$amount, 2, '.', '');
    }
}