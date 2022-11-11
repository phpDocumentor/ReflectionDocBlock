<?php
/*
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */

declare(strict_types=1);

namespace phpDocumentor\Reflection\Assets;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\Types\Context;

class CustomTagFactory implements Factory
{
    public $class;

    public function create(string $tagLine, ?Context $context = null, CustomServiceClass $class = null): Tag
    {
        $this->class = $class;

        return new Generic('custom');
    }
}
