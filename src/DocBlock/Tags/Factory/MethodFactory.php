<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Mixed_;
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
                        $param->type === null ? new Mixed_() : $this->typeResolver->createType(
                            $param->type,
                            $context
                        ),
                        $param->isReference,
                        $param->isVariadic,
                        (string) $param->defaultValue
                    );
                },
                $tagValue->parameters
            ),
        );
    }

    public function supports(PhpDocTagNode $node, Context $context): bool
    {
        return $node->value instanceof MethodTagValueNode;
    }

    private function createReturnType(MethodTagValueNode $tagValue, Context $context): Type
    {
        if ($tagValue->returnType === null) {
            return new Void_();
        }

        return $this->typeResolver->createType($tagValue->returnType, $context);
    }
}
