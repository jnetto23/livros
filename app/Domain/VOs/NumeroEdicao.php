<?php

namespace App\Domain\VOs;

use InvalidArgumentException;

final class NumeroEdicao
{
    private function __construct(
        private readonly int $value
    ) {
        $this->validate();
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(NumeroEdicao $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    private function validate(): void
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('O número da edição deve ser maior que zero.');
        }
    }
}

