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

namespace phpDocumentor\Reflection\DocBlock\Tags;

/**
 * Tag handlers may implement this contract to describe what they expect as input. When the handler rejects a body
 * and an {@see InvalidTag} is produced, the factory forwards these hints to the invalid tag so downstream tooling
 * (for example phpDocumentor's error reporting) can explain the expected syntax instead of only showing the raw
 * exception.
 */
interface ExpectedFormat
{
    /**
     * Returns a short, human-readable description of the expected tag body, e.g. "name [<email>]".
     */
    public static function getExpectedFormat(): string;

    /**
     * Returns a URL pointing to the canonical documentation for the tag, or null when none is available.
     */
    public static function getDocumentationUrl(): ?string;
}
