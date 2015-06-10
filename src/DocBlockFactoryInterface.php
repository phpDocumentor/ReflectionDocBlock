<?php
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
    public static function createInstance(array $additionalTags = []);

    /**
     * @param string $docblock
     * @param DocBlock\Context $context
     * @param DocBlock\Location $location
     *
     * @return DocBlock
     */
    public function create($docblock, DocBlock\Context $context = null, DocBlock\Location $location = null);
}
