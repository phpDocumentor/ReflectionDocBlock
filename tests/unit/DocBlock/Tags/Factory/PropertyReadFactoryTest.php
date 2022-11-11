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
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;

final class PropertyReadFactoryTest extends TagFactoryTestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory::create
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory::supports
     */
    public function testParamIsCreated(): void
    {
        $ast = $this->parseTag('@property-read string $var');
        $factory = new PropertyReadFactory($this->giveTypeResolver(), $this->givenDescriptionFactory());
        $context = new Context('global');

        self::assertTrue($factory->supports($ast, $context));
        self::assertEquals(
            new PropertyRead(
                'var',
                new String_(),
                new Description('')
            ),
            $factory->create($ast, $context)
        );
    }
}
