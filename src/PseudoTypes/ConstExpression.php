<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PseudoTypes;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\PseudoType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Mixed_;

use function sprintf;

final class ConstExpression implements PseudoType
{
    private Fqsen $owner;
    private string $expression;

    public function __construct(Fqsen $owner, string $expression)
    {
        $this->owner = $owner;
        $this->expression = $expression;
    }

    public function getOwner(): Fqsen
    {
        return $this->owner;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function underlyingType(): Type
    {
        return new Mixed_();
    }

    public function __toString(): string
    {
        return sprintf('%s::%s', $this->owner, $this->expression);
    }
}
