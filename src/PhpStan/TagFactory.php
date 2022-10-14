<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\PhpStan;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory as TagFactoryInterface;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\Types\Context as TypeContext;

class TagFactory implements TagFactoryInterface
{
    public function addParameter(string $name, $value) : void
    {
        // TODO: Implement addParameter() method.
    }

    public function create(string $tagLine, ?TypeContext $context = null) : Tag
    {
        return InvalidTag::create($tagLine);
    }

    public function addService(object $service) : void
    {
        // TODO: Implement addService() method.
    }

    public function registerTagHandler(string $tagName, string $handler) : void
    {
        // TODO: Implement registerTagHandler() method.
    }
}
