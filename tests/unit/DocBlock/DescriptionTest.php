<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Description
 */
class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::render
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     */
    public function testDescriptionCanRenderUsingABodyWithPlaceholdersAndTags()
    {
        $body = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant } body.';
        $tags = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        // without formatter (thus the PassthroughFormatter by default)
        $this->assertSame($expected, $fixture->render());

        // with a custom formatter
        $formatter = m::mock(PassthroughFormatter::class);
        $formatter->shouldReceive('format')->with($tags[0])->andReturn('@internal significant ');
        $this->assertSame($expected, $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::render
     * @covers ::__toString
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     */
    public function testDescriptionCanBeCastToString()
    {
        $body = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant } body.';
        $tags = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        $this->assertSame($expected, (string)$fixture);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testBodyTemplateMustBeAString()
    {
        new Description([]);
    }
}
