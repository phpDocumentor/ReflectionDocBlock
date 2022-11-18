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
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;

final class VarFactoryTest extends TagFactoryTestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory::create
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory::supports
     */
    public function testVarIsCreated(): void
    {
        $ast = $this->parseTag('@var string $var');
        $factory = new VarFactory($this->giveTypeResolver(), $this->givenDescriptionFactory());
        $context = new Context('global');

        self::assertTrue($factory->supports($ast, $context));
        self::assertEquals(
            new Var_(
                'var',
                new String_(),
                new Description('')
            ),
            $factory->create($ast, $context)
        );
    }
}
