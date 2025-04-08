<?php

namespace App\Service\Commission;

use DateTime;

class SessionHandlerService
{
    private array $userWeeklyData = [];

    public function getUserWeeklyWithdrawals(int $userId, DateTime $weekStart): array
    {
        $weekKey = $this->generateWeekKey($userId, $weekStart);
        return $this->userWeeklyData[$weekKey] ?? ['totalWithdrawn' => 0, 'count' => 0, 'clientCharged' => 0];
    }

    public function updateUserWeeklyWithdrawals(int $userId, DateTime $weekStart, float $totalWithdrawn, int $withdrawCount, int $clientCharged): void
    {
        $weekKey = $this->generateWeekKey($userId, $weekStart);
        $this->userWeeklyData[$weekKey] = [
            'totalWithdrawn' => $totalWithdrawn,
            'count' => $withdrawCount,
            'clientCharged' => $clientCharged
        ];
    }

    private function generateWeekKey(int $userId, DateTime $weekStart): string
    {
        return $userId . '-' . $weekStart->format('Y-m-d');
    }
}