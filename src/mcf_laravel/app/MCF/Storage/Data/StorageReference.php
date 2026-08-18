<?php

namespace App\MCF\Storage\Data;

use InvalidArgumentException;

final class StorageReference
{
    private const TIMESTAMP_LENGTH = 20;

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function generate(string $extension): self
    {
        $extension = self::normalizeExtension($extension);

        if ($extension === '') {
            throw new InvalidArgumentException(
                'Storage reference requires a valid file extension.'
            );
        }

        $timestamp = now()->format('YmdHis') . now()->format('u');

        return new self(
            $timestamp . '.' . $extension
        );
    }

    public static function fromString(string $reference): self
    {
        $reference = trim($reference);

        if (!self::isValid($reference)) {
            throw new InvalidArgumentException(
                'Invalid storage reference.'
            );
        }

        return new self($reference);
    }

    public static function isValid(string $reference): bool
    {
        return preg_match(
            '/^\d{20}\.[a-z0-9]{1,20}$/i',
            trim($reference)
        ) === 1;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function extension(): string
    {
        return strtolower(
            pathinfo($this->value, PATHINFO_EXTENSION)
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function normalizeExtension(
        string $extension
    ): string {
        return strtolower(
            ltrim(trim($extension), '.')
        );
    }
}
