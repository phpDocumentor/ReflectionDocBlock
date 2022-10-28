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
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;

class CustomTagFactory implements TagFactory
{
    public $class;

    public function addParameter(string $name, $value): void
    {
        // TODO: Implement addParameter() method.
    }

    public function create(string $tagLine, ?Context $context = null, CustomServiceClass $class = null): Tag
    {
        $this->class = $class;

        return new Generic('custom');
    }

    public function addService(object $service): void
    {
        // TODO: Implement addService() method.
    }

    public function registerTagHandler(string $tagName, string $handler): void
    {
        // TODO: Implement registerTagHandler() method.
    }
}
