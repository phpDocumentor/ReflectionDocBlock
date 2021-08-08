<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Link as LinkTag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Version;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter
 */
class AlignFormatterTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Link
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Version
     * @uses   \phpDocumentor\Reflection\Types\String_
     *
     * @covers ::format
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter::__construct
     */
    public function testFormatterCallsToStringAndReturnsAStandardRepresentation(): void
    {
        $tags    = [
            new Param('foobar', new String_()),
            new Version('1.2.0'),
            new LinkTag('http://www.example.com', new Description('Examples')),
        ];
        $fixture = new AlignFormatter($tags);

        $expected = [
            '@param   string $foobar',
            '@version 1.2.0',
            '@link    http://www.example.com Examples',
        ];

        foreach ($tags as $key => $tag) {
            $this->assertSame(
                $expected[$key],
                $fixture->format($tag)
            );
        }
    }
}
