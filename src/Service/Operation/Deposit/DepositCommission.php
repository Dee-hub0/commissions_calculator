<?php

namespace App\Service\Operation\Deposit;

use App\Dto\OperationDto;
use App\Model\CommissionCalculatorInterface;

class DepositCommission implements CommissionCalculatorInterface
{
    private const COMMISSION_RATE = 0.0003;

    public function calculate(OperationDto $operationDto): float
    {
        $commission = $operationDto->getAmount() * self::COMMISSION_RATE;
        return round($commission, 2);
    }
}