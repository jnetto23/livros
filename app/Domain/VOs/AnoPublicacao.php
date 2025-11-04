<?php

namespace App\Domain\VOs;

use InvalidArgumentException;

final class AnoPublicacao
{
    private const LENGTH = 4;
    private const MIN_YEAR = 1455; // Bíblia de Gutenberg - primeiro livro impresso

    private static function getMaxYear(): int
    {
        return (int) date('Y');
    }

    private function __construct(
        private readonly string $value
    ) {
        $this->validate();
    }

    public static function create(string|int $value): self
    {
        return new self((string) $value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function equals(AnoPublicacao $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if (empty($trimmed)) {
            throw new InvalidArgumentException('O ano de publicação não pode ser vazio.');
        }

        if (mb_strlen($trimmed) !== self::LENGTH) {
            throw new InvalidArgumentException(
                sprintf('O ano de publicação deve ter exatamente %d caracteres.', self::LENGTH)
            );
        }

        if (!ctype_digit($trimmed)) {
            throw new InvalidArgumentException('O ano de publicação deve conter apenas dígitos.');
        }

        $yearInt = (int) $trimmed;
        $maxYear = self::getMaxYear();

        if ($yearInt < self::MIN_YEAR || $yearInt > $maxYear) {
            throw new InvalidArgumentException(
                sprintf('O ano de publicação deve estar entre %d e %d.', self::MIN_YEAR, $maxYear)
            );
        }
    }
}

