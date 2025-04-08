<?php

namespace App\Service\Operation\Withdraw;

use App\Dto\OperationDto;
use App\Model\CommissionCalculatorInterface;
use App\Service\Currency\ExchangeRateService;
use App\Service\Commission\SessionHandlerService;
use DateTime;
use Psr\Log\LoggerInterface;

class PrivateClientWithdrawCalculator implements CommissionCalculatorInterface
{
    private const WEEKLY_FREE_LIMIT = 1000.00; // EUR
    private const COMMISSION_RATE = 0.003;
    private const DEFAULT_CURRENCY = 'EUR';

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

    /**
     * Calculate the commission for a withdrawal operation.
     *
     * @param OperationDto
     * @return float
     */
    public function calculate(OperationDto $operationDto): float
    {
        $weekStart = $this->getStartOfWeek($operationDto->getOperationDate());
        $userId = $operationDto->getUserId();
        $weeklyWithdrawals = $this->sessionHandler->getUserWeeklyWithdrawals($userId, $weekStart);

        $commission = $this->calculateCommission($operationDto, $weeklyWithdrawals, $weekStart);

        return $commission;
    }

    /**
     * Calculates the commission for a withdrawal operation.
     *
     * @param OperationDto $operationDto
     * @param array $weeklyWithdrawals
     * @param DateTime $weekStart
     * @return float
     */
    private function calculateCommission(OperationDto $operationDto, array $weeklyWithdrawals, DateTime $weekStart): float
    {
        $withdrawCountThisWeek = $weeklyWithdrawals['count'] + 1;
        $amount = $operationDto->getAmount();
        $commission = 0;
        $clientCharged = 0;
        $converted = false;

        // If the withdrawal count is within the free limit (<= 3 withdrawals)
        if ($withdrawCountThisWeek <= 3) {
            // If the currency is different from the default and the amount exceeds the free limit
            if ($operationDto->getCurrency() !== self::DEFAULT_CURRENCY and $amount >= self::WEEKLY_FREE_LIMIT) {
                try {
                    // Convert the amount to the default currency (EUR)
                    $amount = $this->exchangeRateService->convert($amount, $operationDto->getCurrency(), self::DEFAULT_CURRENCY, false);
                    $converted = true;
                } catch (\Exception $e) {
                    $this->logger->error('An error occurred while processing the operation.', [
                        'exception' => $e,
                        'message' => $e->getMessage()
                    ]);
                }
            }
            // Calculate the total withdrawn amount for the week
            $totalWithdrawnThisWeek = $weeklyWithdrawals['totalWithdrawn'] + $amount;

            // If the total withdrawn this week exceeds the free limit, apply commission
            if ($totalWithdrawnThisWeek > self::WEEKLY_FREE_LIMIT) {
                 // Calculate excess commission for the amount over the free limit
                $commissionData = $this->calculateExcessCommission($weeklyWithdrawals, $totalWithdrawnThisWeek);
                $commission = $commissionData['commission'];
                $clientCharged = $commissionData['commissionAmount'];
            }
        } else {
             // Apply commission on the full withdrawal amount if more than 3 withdrawals this week
            $commission = $amount * self::COMMISSION_RATE;
            $clientCharged =  $amount;
        }

        // Update the user's weekly withdrawal history
        $this->sessionHandler->updateUserWeeklyWithdrawals(
            $operationDto->getUserId(),
            $weekStart,
            $weeklyWithdrawals['totalWithdrawn'] + $amount,
            $withdrawCountThisWeek,
            $weeklyWithdrawals['clientCharged'] + $clientCharged
        );

         // If the amount was converted, convert the commission back to the original currency
        if ($converted) {
            try {
                $commission = $this->exchangeRateService->convert($commission, self::DEFAULT_CURRENCY, $operationDto->getCurrency(), true);
             } catch (\Exception $e) {
                $this->logger->error('An error occurred while processing the operation.', [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $commission;
    }

    /**
     * Calculates the commission on the excess withdrawal amount (above the free limit).
     *
     * @param array $weeklyWithdrawals
     * @param float $totalWithdrawnThisWeek
     * @return array
     */
    private function calculateExcessCommission(array $weeklyWithdrawals, float $totalWithdrawnThisWeek): array
    {
        $chargedAmount = $totalWithdrawnThisWeek - $weeklyWithdrawals['clientCharged'];
        $excessAmount = max(0, $chargedAmount - self::WEEKLY_FREE_LIMIT);
        return [
            'commission' => $excessAmount * self::COMMISSION_RATE,
            'commissionAmount' => $excessAmount
        ];
    }

    /**
     * Gets the start of the week for the given date.
     *
     * @param DateTime
     * @return DateTime
     */
    private function getStartOfWeek(DateTime $date): DateTime
    {
        $startOfWeek = clone $date;
        $startOfWeek->modify('monday this week');
        return $startOfWeek;
    }
}