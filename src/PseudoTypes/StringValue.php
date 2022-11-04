<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Float_;

use function sprintf;

class StringValue implements PseudoType
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function underlyingType(): Type
    {
        return new Float_();
    }

    public function __toString(): string
    {
        return sprintf('"%s"', $this->value);
    }
}
