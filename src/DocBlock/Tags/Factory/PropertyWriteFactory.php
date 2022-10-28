<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Webmozart\Assert\Assert;

use function trim;

/**
 * @internal This class is not part of the BC promise of this library.
 */
final class PropertyWriteFactory implements PHPStanFactory
{
    private TypeFactory $typeFactory;
    private DescriptionFactory $descriptionFactory;

    public function __construct(TypeFactory $typeFactory, DescriptionFactory $descriptionFactory)
    {
        $this->typeFactory = $typeFactory;
        $this->descriptionFactory = $descriptionFactory;
    }

    public function create(PhpDocTagNode $node, ?Context $context): Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, PropertyTagValueNode::class);

        return new PropertyWrite(
            trim($tagValue->propertyName, '$'),
            $this->typeFactory->createType($tagValue->type, $context),
            $this->descriptionFactory->create($tagValue->description, $context)
        );
    }

    public function supports(PhpDocTagNode $node, ?Context $context): bool
    {
        return $node->value instanceof PropertyTagValueNode && $node->name === '@property-write';
    }
}
