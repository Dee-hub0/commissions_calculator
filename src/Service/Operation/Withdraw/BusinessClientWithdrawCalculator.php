<?php

namespace App\Service\Operation\Withdraw;

use App\Dto\OperationDto;
use App\Model\CommissionCalculatorInterface;

class BusinessClientWithdrawCalculator implements CommissionCalculatorInterface
{
    private const COMMISSION_PERCENTAGE = 0.005;

    public function calculate(OperationDto $operationDto): float
    {
        $commission = $operationDto->getAmount() * self::COMMISSION_PERCENTAGE;
        return round($commission, 2);
    }
}