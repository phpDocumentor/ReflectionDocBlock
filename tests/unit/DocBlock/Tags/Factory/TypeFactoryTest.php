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
use phpDocumentor\Reflection\PseudoTypes\ConstExpression;
use phpDocumentor\Reflection\PseudoTypes\FloatValue;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\IntegerValue;
use phpDocumentor\Reflection\PseudoTypes\List_;
use phpDocumentor\Reflection\PseudoTypes\StringValue;
use phpDocumentor\Reflection\PseudoTypes\True_;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\ArrayKey;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\CallableParameter;
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
     * @covers       \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createType
     * @covers       \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createFromGeneric
     * @covers       \phpDocumentor\Reflection\DocBlock\Tags\Factory\TypeFactory::createFromCallable
     * @dataProvider typeProvider
     * @dataProvider genericsProvider
     * @dataProvider callableProvider
     * @dataProvider constExpressions
     * @testdox create type from $type
     */
    public function testTypeBuilding(string $type, Type $expected): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize($type);
        $constParser = new ConstExprParser();
        $parser = new TypeParser($constParser);
        $ast = $parser->parse(new TokenIterator($tokens));
        $fqsenResolver = new FqsenResolver();

        $factory = new TypeFactory(new TypeResolver($fqsenResolver), $fqsenResolver);
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
                new Callable_([], new Object_(new Fqsen('\\phpDocumentor\\Foo'))),
            ],
            [
                'callable(): (Foo&Bar)',
                new Callable_(
                    [],
                    new Intersection(
                        [
                            new Object_(new Fqsen('\\phpDocumentor\\Foo')),
                            new Object_(new Fqsen('\\phpDocumentor\\Bar'))
                        ]
                    )
                ),
            ],
            [
                'callable(A&...$a=, B&...=, C): Foo',
                new Callable_(
                    [
                        new CallableParameter(
                            'a',
                            new Object_(new Fqsen('\\phpDocumentor\\A')),
                            true,
                            true,
                            true
                        ),
                        new CallableParameter(
                            null,
                            new Object_(new Fqsen('\\phpDocumentor\\B')),
                            true,
                            true,
                            true
                        ),
                        new CallableParameter(
                            null,
                            new Object_(new Fqsen('\\phpDocumentor\\C')),
                            false,
                            false,
                            false
                        ),
                    ],
                    new Object_(new Fqsen('\\phpDocumentor\\Foo')
                    )
                ),
            ],
        ];
    }

    /**
     * @return array<array{0: string, 1: Type}>
     */
    public function constExpressions(): array
    {
        return [
            [
                '123',
                new IntegerValue(123),
            ],
            [
                'true',
                new True_(),
            ],
            [
                '123.2',
                new FloatValue(123.2),
            ],
            [
                '"bar"',
                new StringValue('bar'),
            ],
            [
                'Foo::FOO_CONSTANT',
                new ConstExpression(new Fqsen('\\phpDocumentor\\Foo'), 'FOO_CONSTANT'),
            ],
            [
                'Foo::FOO_*',
                new ConstExpression(new Fqsen('\\phpDocumentor\\Foo'), 'FOO_*'),
            ],
        ];
    }
}
