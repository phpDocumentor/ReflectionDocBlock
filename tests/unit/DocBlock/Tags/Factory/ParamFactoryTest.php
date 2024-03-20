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
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\String_;

final class ParamFactoryTest extends TagFactoryTestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory::create
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory::supports
     * @dataProvider paramInputProvider
     */
    public function testParamIsCreated(string $input, Param $expected): void
    {
        $ast = $this->parseTag($input);
        $factory = new ParamFactory($this->giveTypeResolver(), $this->givenDescriptionFactory());
        $context = new Context('global');

        self::assertTrue($factory->supports($ast, $context));
        self::assertEquals(
            $expected,
            $factory->create($ast, $context)
        );
    }

    /**
     * @return array<array-key, string|Param>
     */
    public function paramInputProvider(): array
    {
        return [
            [
                '@param string $var',
                new Param(
                    'var',
                    new String_(),
                    false,
                    new Description(''),
                    false
                ),
            ],
            [
                '@param $param8 Description 4',
                new Param(
                    'param8',
                    new Mixed_(),
                    false,
                    new Description('Description 4'),
                    false
                ),
            ],
            [
                '@param $param9',
                new Param(
                    'param9',
                    new Mixed_(),
                    false,
                    new Description(''),
                    false
                ),
            ],
            [
                '@param int My Description',
                new Param(
                    null,
                    new Integer(),
                    false,
                    new Description('My Description'),
                    false
                ),
            ],
        ];
    }
}
