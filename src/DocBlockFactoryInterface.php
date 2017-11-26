<?php declare(strict_types=1);

namespace phpDocumentor\Reflection;

interface DocBlockFactoryInterface
{
    /**
     * Factory method for easy instantiation.
     *
     * @param string[] $additionalTags
     *
     * @return DocBlockFactory
     */
    public static function createInstance(array $additionalTags = []): DocBlockFactory;

    /**
     * @param string|object $docblock
     * @param Location $location
     *
     * @return DocBlock
     */
    public function create($docblock, Types\Context $context = null, Location $location = null): DocBlock;
}
