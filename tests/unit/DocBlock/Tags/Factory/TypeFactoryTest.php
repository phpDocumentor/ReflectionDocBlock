<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\List_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ArrayKey;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\ClassString;
use phpDocumentor\Reflection\Types\Collection;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\InterfaceString;
use phpDocumentor\Reflection\Types\Intersection;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\This;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;

final class TypeFactoryTest extends TestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createType
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createFromGeneric
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createFromCallable
     * @dataProvider typeProvider
     * @dataProvider genericsProvider
     * @dataProvider callableProvider
     */
    public function testTypeBuilding(string $type, Type $expected): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize($type);
        $constParser = new ConstExprParser();
        $parser = new TypeParser($constParser);
        $ast = $parser->parse(new TokenIterator($tokens));

        $factory = new TypeFactory(new TypeResolver(new FqsenResolver()));
        $actual = $factory->createType($ast, new Context('phpDocumentor'));

        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<array{0: string, 1: Type}>
     */
    public function typeProvider(): array
    {
        return [
            [
                'string',
                new String_(),
            ],
            [
                '( string )',
                new String_(),
            ],
            [
                '\\Foo\Bar\\Baz',
                new Object_(new Fqsen('\\Foo\Bar\\Baz')),
            ],
            [
                'string|int',
                new Compound(
                    [
                        new String_(),
                        new Integer(),
                    ]
                ),
            ],
            [
                'string&int',
                new Intersection(
                    [
                        new String_(),
                        new Integer(),
                    ]
                ),
            ],
            [
                'string & (int | float)',
                new Intersection(
                    [
                        new String_(),
                        new Compound(
                            [
                                new Integer(),
                                new Float_(),
                            ]
                        ),
                    ]
                ),
            ],
            [
                'string[]',
                new Array_(
                    new String_()
                ),
            ],
            [
                '$this',
                new This(),
            ],
            [
                '?int',
                new Nullable(
                    new Integer()
                ),
            ],
            [
                'self',
                new Self_(),
            ],
        ];
    }

    /**
     * @return array<array{0: string, 1: Type}>
     */
    public function genericsProvider(): array
    {
        return [
            [
                'array<int, Foo\\Bar>',
                new Array_(
                    new Object_(new Fqsen('\\phpDocumentor\\Foo\\Bar')),
                    new Integer()
                ),
            ],
            [
                'Collection<array-key, int>[]',
                new Array_(
                    new Collection(
                        new Fqsen('\\phpDocumentor\\Collection'),
                        new Integer(),
                        new ArrayKey()
                    )
                ),
            ],
            [
                'class-string',
                new ClassString(null),
            ],
            [
                'class-string<Foo>',
                new ClassString(new Fqsen('\\phpDocumentor\\Foo')),
            ],
            [
                'interface-string<Foo>',
                new InterfaceString(new Fqsen('\\phpDocumentor\\Foo')),
            ],
            [
                'List<Foo>',
                new List_(new Object_(new Fqsen('\\phpDocumentor\\Foo'))),
            ],
            [
                'int<1, 100>',
                new IntegerRange('1', '100'),
            ],
        ];
    }

    /**
     * @return array<array{0: string, 1: Type}>
     */
    public function callableProvider(): array
    {
        return [
            [
                'callable',
                new Callable_(),
            ],
            [
                'callable()',
                new Callable_(),
            ],
            [
                'callable(): Foo',
                new Callable_(),
            ],
            [
                'callable(): (Foo&Bar)',
                new Callable_(),
            ],
            [
                'callable(A&...$a=, B&...=, C): Foo',
                new Callable_(),
            ],
        ];
    }

    /**
     * @return array<array{0: string, 1: Type}>
     */
    public function constExpressions(): array
    {
        return [
            ['Foo::FOO_CONSTANT'],
            [
                '123',
                //new ConstTypeNode(new ConstExprIntegerNode('123')),
            ],
            [
                '123.2',
                //new ConstTypeNode(new ConstExprFloatNode('123.2')),
            ],
            [
                '"bar"',
                //new ConstTypeNode(new ConstExprStringNode('bar')),
            ],
            [
                'Foo::FOO_*',
                //new ConstTypeNode(new ConstFetchNode('Foo', 'FOO_*')),
            ],
            [
                'Foo::FOO_*BAR',
                //new ConstTypeNode(new ConstFetchNode('Foo', 'FOO_*BAR')),
            ],
            [
                'Foo::*FOO*',
                //new ConstTypeNode(new ConstFetchNode('Foo', '*FOO*')),
            ],
            [
                'Foo::A*B*C',
                //new ConstTypeNode(new ConstFetchNode('Foo', 'A*B*C')),
            ],
            [
                'self::*BAR',
                //new ConstTypeNode(new ConstFetchNode('self', '*BAR')),
            ],
            [
                'Foo::*',
                //new ConstTypeNode(new ConstFetchNode('Foo', '*')),
            ],
            [
                'Foo::**',
                //new ConstTypeNode(new ConstFetchNode('Foo', '*')), // fails later in PhpDocParser
                //Lexer::TOKEN_WILDCARD,
            ],
            [
                'Foo::*a',
                //new ConstTypeNode(new ConstFetchNode('Foo', '*a')),
            ],
            [
                '( "foo" | Foo::FOO_* )',
//                new UnionTypeNode([
//                    new ConstTypeNode(new ConstExprStringNode('foo')),
//                    new ConstTypeNode(new ConstFetchNode('Foo', 'FOO_*')),
//                ]),
            ],
            [
                'DateTimeImmutable::*|DateTime::*',
//                new UnionTypeNode([
//                    new ConstTypeNode(new ConstFetchNode('DateTimeImmutable', '*')),
//                    new ConstTypeNode(new ConstFetchNode('DateTime', '*')),
//                ]),
            ],
            [
                'ParameterTier::*|null',
//                new UnionTypeNode([
//                    new ConstTypeNode(new ConstFetchNode('ParameterTier', '*')),
//                    new IdentifierTypeNode('null'),
//                ]),
            ],
        ];
    }
}
