<?php

namespace App\Factory;

use App\Service\Operation\Deposit\DepositCommission;
use App\Service\Operation\Withdraw\PrivateClientWithdrawCalculator;
use App\Service\Operation\Withdraw\BusinessClientWithdrawCalculator;
use App\Model\CommissionCalculatorInterface;
use App\Service\Currency\ExchangeRateService;
use App\Service\Commission\SessionHandlerService;
use Psr\Log\LoggerInterface;

class OperationFactory
{
    private SessionHandlerService $sessionHandler;
    private ExchangeRateService $exchangeRateService;
    private LoggerInterface $logger;

    public function __construct(
        SessionHandlerService $sessionHandler,
        ExchangeRateService $exchangeRateService,
        LoggerInterface $logger
    ) {
        $this->sessionHandler = $sessionHandler;
        $this->exchangeRateService = $exchangeRateService;
        $this->logger = $logger;
    }

    public function create(string $operationType, string $userType): CommissionCalculatorInterface
    {
        switch ($operationType) {
            case 'deposit':
                return new DepositCommission();
            case 'withdraw':
                return $this->createWithdrawOperation($userType);
            default:
                throw new \InvalidArgumentException("Unknown operation type: $operationType");
        }
    }

    private function createWithdrawOperation(string $userType): CommissionCalculatorInterface
    {
        switch ($userType) {
            case 'private':
                return new PrivateClientWithdrawCalculator(
                    $this->sessionHandler,
                    $this->exchangeRateService,
                    $this->logger
                );
            case 'business':
                return new BusinessClientWithdrawCalculator();
            default:
                throw new \InvalidArgumentException("Unknown user type: $userType");
        }
    }
}