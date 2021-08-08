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
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
 */
class PassthroughFormatterTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::format
     */
    public function testFormatterCallsToStringAndReturnsAStandardRepresentation(): void
    {
        $expected = '@unknown-tag This is a description';

        $fixture = new PassthroughFormatter();

        $this->assertSame(
            $expected,
            $fixture->format(new Generic('unknown-tag', new Description('This is a description')))
        );
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     *
     * @covers ::format
     */
    public function testFormatterToStringWithoutDescription(): void
    {
        $expected = '@unknown-tag';
        $fixture  = new PassthroughFormatter();

        $this->assertSame(
            $expected,
            $fixture->format(new Generic('unknown-tag'))
        );
    }
}
