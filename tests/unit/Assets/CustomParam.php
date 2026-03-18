<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\Assets;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\FqsenResolver;

final class CustomParam implements Tag
{
    /** @var string|null */
    public $myParam;

    /** @var FqsenResolver|null */
    public $fqsenResolver;

    public function getName() : string
    {
        return 'spy';
    }

    public static function create(string $body, ?FqsenResolver $fqsenResolver = null, ?string $myParam = null)
    {
        $tag = new self();
        $tag->fqsenResolver = $fqsenResolver;
        $tag->myParam = $myParam;

        return $tag;
    }

    public function render(?Formatter $formatter = null) : string
    {
        return $this->getName();
    }

    public function __toString() : string
    {
        return $this->getName();
    }
}
