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

use phpDocumentor\Reflection\Fqsen as RealFqsen;

/**
 * Fqsen reference used by {@see \phpDocumentor\Reflection\DocBlock\Tags\See}
 */
final class Fqsen implements Reference
{
    private RealFqsen $fqsen;

    private ?string $bookmark;

    public function __construct(RealFqsen $fqsen, ?string $bookmark = null)
    {
        $this->fqsen = $fqsen;
        $this->bookmark = $bookmark;
    }

    /**
     * Returns the bookmark suffix declared with `#<bookmark>` in the docblock, or null when none was given.
     */
    public function getBookmark(): ?string
    {
        return $this->bookmark;
    }

    /**
     * @return string string representation of the referenced fqsen, without the bookmark suffix so
     *                callers can feed it back to {@see \phpDocumentor\Reflection\Fqsen} safely
     */
    public function __toString(): string
    {
        return (string) $this->fqsen;
    }
}
