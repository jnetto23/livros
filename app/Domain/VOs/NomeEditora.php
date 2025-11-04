<?php

namespace App\Domain\VOs;

use InvalidArgumentException;

final class NomeEditora
{
    private const MAX_LENGTH = 40;

    private function __construct(
        private readonly string $value
    ) {
        $this->validate();
    }

    public static function create(string $value): self
    {
        $normalized = self::normalize($value);
        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(NomeEditora $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalize(string $text): string
    {
        // Remove espaços no início e fim
        $text = trim($text);

        // Remove espaços duplos (ou múltiplos) e substitui por espaço simples
        $text = preg_replace('/\s+/', ' ', $text);

        return $text;
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('O nome da editora não pode ser vazio.');
        }

        if (mb_strlen($this->value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('O nome da editora não pode ter mais de %d caracteres.', self::MAX_LENGTH)
            );
        }
    }
}

