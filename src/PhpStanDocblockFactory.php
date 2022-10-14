<?php
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\PhpStan\TagFactory;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\PseudoTypes\ArrayShapeItem;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDoc\Tag\ParamTag;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Webmozart\Assert\Assert;
use InvalidArgumentException;
use LogicException;

final class PhpStanDocblockFactory implements DocBlockFactoryInterface
{
    private PhpDocParser $parser;
    private Lexer $lexer;
    private DescriptionFactory $descriptionFactory;
    private TypeResolver $typeResolver;

    private function __construct()
    {
    }

    public static function createInstance(array $additionalTags = []): DocBlockFactoryInterface
    {
        $fqsenResolver      = new FqsenResolver();
        $constExprParser = new ConstExprParser();
        $self = new self();
        $self->lexer = new Lexer();
        $self->parser = new PhpDocParser(
            new TypeParser($constExprParser),
            $constExprParser
        );

        $self->descriptionFactory = new DescriptionFactory(new TagFactory());
        $self->typeResolver = new TypeResolver($fqsenResolver);

        return $self;
    }

    public function create($docblock, ?Types\Context $context = null, ?Location $location = null): DocBlock
    {
        if (is_object($docblock)) {
            if (!method_exists($docblock, 'getDocComment')) {
                $exceptionMessage = 'Invalid object passed; the given object must support the getDocComment method';

                throw new InvalidArgumentException($exceptionMessage);
            }

            $docblock = $docblock->getDocComment();
            Assert::string($docblock);
        }

        Assert::stringNotEmpty($docblock);

        $tokens = $this->lexer->tokenize($docblock);
        $ast = $this->parser->parse(new TokenIterator($tokens));

        $textNodes = [];

        foreach ($ast->children as $child) {
            if ($child instanceof PhpDocTextNode) {
                $textNodes[] = $child->text;
                continue;
            }

            //If node is not a text node this is the end of description;
            break;
        }

        $tags = [];
        foreach ($ast->getTags() as $node) {
            switch ($node->name) {
                case '@param':
                    $tag = $node->value;
                    $tags[] = new Param(
                        ltrim($tag->parameterName, '$'),
                        $this->createType($tag->type, $context),
                        $tag->isVariadic,
                        $this->descriptionFactory->create($tag->description),
                        $tag->isReference
                    );
                    break;
                case '@return':
                    $tag = $node->value;
                    $tags[] = new Return_(
                        $this->createType($tag->type, $context),
                        $this->descriptionFactory->create($tag->description)
                    );
            }
        }

        return new DocBlock(
            '',
            $this->descriptionFactory->create(
                implode("\n", $textNodes)
            ),
            $tags,
            null,
            $location
        );
    }

    private function createType(TypeNode $type, Context $context)
    {
        switch (get_class($type)) {
            case IdentifierTypeNode::class:
                return $this->typeResolver->resolve($type->name, $context);
            case ArrayShapeNode::class:
                return new ArrayShape(
                    ... array_map(
                        fn(ArrayShapeItemNode $item) => new ArrayShapeItem(
                            (string) $item->keyName,
                            $this->createType($item->valueType, $context),
                            $item->optional
                        ),
                        $type->items
                    )
                );
            default:
                return null;
        }
    }
}
