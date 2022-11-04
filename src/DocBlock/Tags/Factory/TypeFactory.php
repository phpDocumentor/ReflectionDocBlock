<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\PseudoTypes\ArrayShapeItem;
use phpDocumentor\Reflection\PseudoTypes\ConstExpression;
use phpDocumentor\Reflection\PseudoTypes\FloatValue;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\IntegerValue;
use phpDocumentor\Reflection\PseudoTypes\List_;
use phpDocumentor\Reflection\PseudoTypes\StringValue;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\InterfaceString;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\This;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;

use function array_filter;
use function array_map;
use function array_reverse;
use function get_class;
use function strtolower;

/**
 * @internal This class is not part of the BC promise of this library.
 */
final class TypeFactory
{
    private TypeResolver $resolver;
    private FqsenResolver $fqsenResolver;

    public function __construct(TypeResolver $resolver, FqsenResolver $fqsenResolver)
    {
        $this->resolver = $resolver;
        $this->fqsenResolver = $fqsenResolver;
    }

    public function createType(?TypeNode $type, ?Context $context): ?Type
    {
        if ($type === null) {
            return null;
        }

        switch (get_class($type)) {
            case ArrayTypeNode::class:
                return new Array_(
                    $this->createType($type->type, $context)
                );

            case ArrayShapeNode::class:
                return new ArrayShape(
                    ...array_map(
                        fn (ArrayShapeItemNode $item) => new ArrayShapeItem(
                            (string) $item->keyName,
                            $this->createType($item->valueType, $context),
                            $item->optional
                        ),
                        $type->items
                    )
                );

            case CallableTypeNode::class:
                return $this->createFromCallable($type, $context);

            case ConstTypeNode::class:
                return $this->createFromConst($type, $context);

            case GenericTypeNode::class:
                return $this->createFromGeneric($type, $context);

            case IdentifierTypeNode::class:
                return $this->resolver->resolve($type->name, $context);

            case IntersectionTypeNode::class:
                return new Intersection(
                    array_filter(
                        array_map(
                            fn (TypeNode $nestedType) => $this->createType($nestedType, $context),
                            $type->types
                        )
                    )
                );

            case NullableTypeNode::class:
                $nestedType = $this->createType($type->type, $context);
                if ($nestedType === null) {
                    return null;
                }

                return new Nullable($nestedType);

            case UnionTypeNode::class:
                return new Compound(
                    array_filter(
                        array_map(
                            fn (TypeNode $nestedType) => $this->createType($nestedType, $context),
                            $type->types
                        )
                    )
                );

            case ThisTypeNode::class:
                return new This();

            case ConditionalTypeNode::class:
            case ConditionalTypeForParameterNode::class:
            case OffsetAccessTypeNode::class:
            default:
                return null;
        }
    }

    private function createFromGeneric(GenericTypeNode $type, ?Context $context): Type
    {
        switch (strtolower($type->type->name)) {
            case 'array':
                return new Array_(
                    ...array_reverse(
                        array_map(
                            fn (TypeNode $genericType) => $this->createType($genericType, $context),
                            $type->genericTypes
                        )
                    )
                );

            case 'class-string':
                return new ClassString(
                    $this->fqsenResolver->resolve((string) $type->genericTypes[0], $context)
                );

            case 'interface-string':
                return new InterfaceString(
                    $this->fqsenResolver->resolve((string) $type->genericTypes[0], $context)
                );

            case 'list':
                return new List_(
                    $this->createType($type->genericTypes[0], $context)
                );

            case 'int':
                return new IntegerRange(
                    (string) $type->genericTypes[0],
                    (string) ($type->genericTypes[1] ?? ''),
                );

            default:
                return new Collection(
                    $this->createType($type->type, $context)->getFqsen(),
                    ...array_reverse(
                        array_map(
                            fn (TypeNode $genericType) => $this->createType($genericType, $context),
                            $type->genericTypes
                        )
                    )
                );
        }
    }

    private function createFromCallable(CallableTypeNode $type, ?Context $context): Callable_
    {
        return new Callable_();
    }

    private function createFromConst(ConstTypeNode $type, ?Context $context): ?Type
    {
        switch (get_class($type->constExpr)) {
            case ConstExprIntegerNode::class:
                return new IntegerValue((int) $type->constExpr->value);

            case ConstExprFloatNode::class:
                return new FloatValue((float) $type->constExpr->value);

            case ConstExprStringNode::class:
                return new StringValue($type->constExpr->value);

            case ConstFetchNode::class:
                return new ConstExpression(
                    $this->fqsenResolver->resolve($type->constExpr->className, $context),
                    $type->constExpr->name
                );
        }
    }
}
