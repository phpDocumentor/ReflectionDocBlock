<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Float_;

class FloatValue implements PseudoType
{
    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function underlyingType(): Type
    {
        return new Float_();
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
