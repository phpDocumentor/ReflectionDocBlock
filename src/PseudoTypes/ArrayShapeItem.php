<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\Type;

use function sprintf;

final class ArrayShapeItem
{
    private ?string $key;
    private Type $value;
    private bool $optional;

    public function __construct(?string $key, Type $value, bool $optional)
    {
        $this->key = $key;
        $this->value = $value;
        $this->optional = $optional;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getValue(): Type
    {
        return $this->value;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function __toString()
    {
        if ($this->key !== null) {
            return sprintf(
                '%s%s: %s',
                $this->key,
                $this->optional ? '?' : '',
                $this->value
            );
        }

        return (string) $this->value;
    }
}
