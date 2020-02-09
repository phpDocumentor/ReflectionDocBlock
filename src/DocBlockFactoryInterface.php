<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming.SuperfluousSuffix
interface DocBlockFactoryInterface
{
    /**
     * Factory method for easy instantiation.
     *
     * @param array<class-string<StaticMethod>> $additionalTags
     */
    public static function createInstance(array $additionalTags = []) : DocBlockFactory;

    /**
     * @param string|object $docblock
     */
    public function create($docblock, ?Types\Context $context = null, ?Location $location = null) : DocBlock;
}
