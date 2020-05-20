<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\AST;

class Docblock
{
    private $tags;

    /**
     * @var string|null
     */
    private $summary;

    public function __construct(?string $summary = null, Tag ...$tags)
    {
        $this->summary = $summary;
        $this->tags = $tags;
    }
}
