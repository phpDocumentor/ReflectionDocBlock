<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

class AsisFormatter implements Formatter
{
    /**
     * Formats the given tag to return a simple untrimmed plain text version.
     */
    public function format(Tag $tag) : string
    {
        return '@' . $tag->getName() . ' ' . $tag;
    }
}
