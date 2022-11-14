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

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\MethodParameter;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void_;

final class MethodFactoryTest extends TagFactoryTestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory::create
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory::supports
     * @dataProvider tagProvider
     */
    public function testIsCreated(string $tagLine, Method $tag): void
    {
        $ast = $this->parseTag($tagLine);
        $factory = new MethodFactory($this->giveTypeResolver(), $this->givenDescriptionFactory());
        $context = new Context('global');

        self::assertTrue($factory->supports($ast, $context));
        self::assertEquals(
            $tag,
            $factory->create($ast, $context)
        );
    }

    /** @return array<array<string|Method>> */
    public function tagProvider(): array
    {
        return [
            [
                '@method static string myMethod()',
                new Method(
                    'myMethod',
                    [],
                    new String_(),
                    true,
                    new Description(''),
                    false,
                    []
                ),
            ],
            [
                '@method string myMethod()',
                new Method(
                    'myMethod',
                    [],
                    new String_(),
                    false,
                    new Description(''),
                    false,
                    []
                ),
            ],
            [
                '@method myMethod()',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    []
                ),
            ],
            [
                '@method myMethod($a)',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [new MethodParameter('a', new Mixed_())]
                ),
            ],
            [
                '@method void setInteger(integer $integer)',
                new Method(
                    'setInteger',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [new MethodParameter('integer', new Integer())]
                ),
            ],
            [
                '@method myMethod($a = 1)',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [new MethodParameter('a', new Mixed_(), false, false, '1')]
                ),
            ],
            [
                '@method myMethod(int $a = 1)',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [new MethodParameter('a', new Integer(), false, false, '1')]
                ),
            ],
            [
                '@method myMethod(int ...$a)',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [new MethodParameter('a', new Integer(), false, true)]
                ),
            ],
            [
                '@method myMethod(int &$a, string $b)',
                new Method(
                    'myMethod',
                    [],
                    new Void_(),
                    false,
                    new Description(''),
                    false,
                    [
                        new MethodParameter('a', new Integer(), true, false),
                        new MethodParameter('b', new String_(), false, false),
                    ]
                ),
            ],
        ];
    }
}
