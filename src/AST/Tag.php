<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\AST;

class Tag
{
    private $name;
    private $specialization;

    public function __construct(string $name, ?string $specialization = null)
    {
        $this->name = $name;
        $this->specialization = $specialization;
    }
}
