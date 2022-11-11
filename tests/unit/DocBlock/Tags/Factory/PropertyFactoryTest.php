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
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;

final class PropertyFactoryTest extends TagFactoryTestCase
{
    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory::create
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory::supports
     */
    public function testParamIsCreated(): void
    {
        $ast = $this->parseTag('@property string $var');
        $factory = new PropertyFactory($this->giveTypeResolver(), $this->givenDescriptionFactory());
        $context = new Context('global');

        self::assertTrue($factory->supports($ast, $context));
        self::assertEquals(
            new Property(
                'var',
                new String_(),
                new Description('')
            ),
            $factory->create($ast, $context)
        );
    }
}
