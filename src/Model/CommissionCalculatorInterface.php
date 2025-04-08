<?php

namespace App\Model;

use App\Dto\OperationDto;

interface CommissionCalculatorInterface
{
    public function calculate(OperationDto $operationDto): float;
}