<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

class OperationDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Date(format="Y-m-d")
     */
    private DateTime $operationDate;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\GreaterThan(0)
     */
    private int $userId;

    /**
     * @Assert\NotBlank
     * @Assert\Choice({"private", "business"})
     */
    private string $userType;

    /**
     * @Assert\NotBlank
     * @Assert\Choice({"deposit", "withdraw"})
     */
    private string $operationType;

    /**
     * @Assert\NotBlank
     * @Assert\Type("float")
     * @Assert\GreaterThan(0)
     */
    private float $amount;

    /**
     * @Assert\NotBlank
     * @Assert\Currency
     */
    private string $currency;

    public function __construct(
        DateTime $operationDate,
        int $userId,
        string $userType,
        string $operationType,
        float $amount,
        string $currency
    ) {
        $this->operationDate = $operationDate;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getOperationDate(): DateTime
    {
        return $this->operationDate;
    }

    public function setOperationDate(DateTime $operationDate): self
    {
        $this->operationDate = $operationDate;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): self
    {
        $this->userType = $userType;
        return $this;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): self
    {
        $this->operationType = $operationType;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }
}