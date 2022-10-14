<?php
/*
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

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\PseudoTypes\ArrayShapeItem;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

class PhpStanDocblockFactoryTest extends TestCase
{
    public function testDocblockIsParsed()
    {
        $docblock = new DocBlock(
            'test summary',
            new Description(
                "This description contains tags \n And is multiline"
            ),
            [
                new Param('firstParam', new String_(), false, new Description('Some description'), false),
                new Return_(new ArrayShape(new ArrayShapeItem('foo', new String_(), false)))
            ]
        );

        $string = <<<DOC
/**
 * test summary
 *
 * THis description contains tags {@see https://phpdoc.org with a description}
 * And is multi line
 *
 * @param string \$firstParam Some description
 * @return array{foo: string}
 */
DOC;


        $factory = PhpStanDocblockFactory::createInstance();
        $actual = $factory->create(
            $string,
            new Context('/')
        );

        self::assertEquals($docblock, $actual);
    }
}
