<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Void_;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Webmozart\Assert\Assert;

use function array_map;
use function trim;

/**
 * @internal This class is not part of the BC promise of this library.
 */
final class MethodFactory implements PHPStanFactory
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
        Assert::isInstanceOf($tagValue, MethodTagValueNode::class);

        return new Method(
            $tagValue->methodName,
            [],
            $this->createReturnType($tagValue, $context),
            $tagValue->isStatic,
            $this->descriptionFactory->create($tagValue->description, $context),
            false,
            array_map(
                function (MethodTagValueParameterNode $param) use ($context) {
                    return new MethodParameter(
                        trim($param->parameterName, '$'),
                        $this->typeFactory->createType($param->type, $context),
                        $param->isReference,
                        $param->isVariadic,
                        (string) $param->defaultValue
                    );
                },
                $tagValue->parameters
            ),
        );
    }

    public function supports(PhpDocTagNode $node, ?Context $context): bool
    {
        return $node->value instanceof MethodTagValueNode;
    }

    private function createReturnType(MethodTagValueNode $tagValue, ?Context $context): Type
    {
        return $this->typeFactory->createType($tagValue->returnType, $context) ?? new Void_();
    }
}
