<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags\Reference;

use Webmozart\Assert\Assert;

/**
 * Variable reference used by {@see \phpDocumentor\Reflection\DocBlock\Tags\See} to refer to a variable that
 * is not addressable through an FQSEN, typically a global variable such as {@example @see $varname}.
 */
final class Variable implements Reference
{
    private string $name;

    public function __construct(string $name)
    {
        Assert::stringNotEmpty($name);
        Assert::startsWith($name, '$');

        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
