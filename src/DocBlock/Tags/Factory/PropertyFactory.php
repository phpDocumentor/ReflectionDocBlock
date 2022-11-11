<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Webmozart\Assert\Assert;

use function trim;

/**
 * @internal This class is not part of the BC promise of this library.
 */
final class PropertyFactory implements PHPStanFactory
{
    private DescriptionFactory $descriptionFactory;
    private TypeResolver $typeResolver;

    public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->typeResolver = $typeResolver;
    }

    public function create(PhpDocTagNode $node, Context $context): Tag
    {
        $tagValue = $node->value;
        Assert::isInstanceOf($tagValue, PropertyTagValueNode::class);

        return new Property(
            trim($tagValue->propertyName, '$'),
            $this->typeResolver->createType($tagValue->type, $context),
            $this->descriptionFactory->create($tagValue->description, $context)
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof PropertyTagValueNode && $node->name === '@property';
    }
}
