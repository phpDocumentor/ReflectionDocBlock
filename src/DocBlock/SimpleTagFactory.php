<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use phpDocumentor\Reflection\Types\Context as TypeContext;

interface SimpleTagFactory
{

    /**
     * Factory method responsible for instantiating the correct sub type.
     *
     * @param string $tagLine The text for this tag, including description.
     *
     * @return Tag A new tag object.
     *
     * @throws InvalidArgumentException If an invalid tag line was presented.
     */
    public function create(string $tagLine, ?TypeContext $context = null): Tag;
}
