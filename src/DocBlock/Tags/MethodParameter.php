<?php
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 */

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\Type;

final class MethodParameter
{
    private Type $type;

    private bool $isReference;

    private bool $isVariadic;

    private string $name;

    private ?string $defaultValue = null;

    public function __construct(
        string $name,
        Type $type,
        bool $isReference = false,
        bool $isVariadic = false,
        ?string $defaultValue = null
    ) {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->name = $name;
        $this->defaultValue = $defaultValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function isReference(): bool
    {
        return $this->isReference;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }
}
