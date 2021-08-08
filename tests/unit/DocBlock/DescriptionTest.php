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

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Description
 */
class DescriptionTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     *
     * @covers ::__construct
     * @covers ::render
     */
    public function testDescriptionCanRenderUsingABodyWithPlaceholdersAndTags(): void
    {
        $body     = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant} body.';
        $tags     = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        // without formatter (thus the PassthroughFormatter by default)
        $this->assertSame($expected, $fixture->render());

        // with a custom formatter
        $formatter = m::mock(PassthroughFormatter::class);
        $formatter->shouldReceive('format')->with($tags[0])->andReturn('@internal significant');
        $this->assertSame($expected, $fixture->render($formatter));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     *
     * @covers ::__construct
     * @covers ::render
     * @covers ::__toString
     */
    public function testDescriptionCanBeCastToString(): void
    {
        $body     = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant} body.';
        $tags     = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        $this->assertSame($expected, (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::getTags
     */
    public function testDescriptionTagsGetter(): void
    {
        $body = '@JoinTable(name="table", joinColumns=%1$s, inverseJoinColumns=%2$s)';

        $tag1 = new Generic('JoinColumn', new Description('(name="column_id", referencedColumnName="id")'));
        $tag2 = new Generic('JoinColumn', new Description('(name="column_id_2", referencedColumnName="id")'));

        $tags = [
            $tag1,
            $tag2,
        ];

        $fixture = new Description($body, $tags);

        $this->assertCount(2, $fixture->getTags());

        $actualTags = $fixture->getTags();
        $this->assertSame($tags, $actualTags);
        $this->assertSame($tag1, $actualTags[0]);
        $this->assertSame($tag2, $actualTags[1]);
    }

    /**
     * @covers ::getBodyTemplate
     */
    public function testDescriptionBodyTemplateGetter(): void
    {
        $body = 'See https://github.com/phpDocumentor/ReflectionDocBlock/pull/171 for more information';

        $fixture = new Description($body, []);

        $this->assertSame($body, $fixture->getBodyTemplate());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     *
     * @covers ::__construct
     * @covers ::render
     * @covers ::__toString
     */
    public function testDescriptionMultipleTagsCanBeCastToString(): void
    {
        $body = '@JoinTable(name="table", joinColumns=%1$s, inverseJoinColumns=%2$s)';

        $tag1 = new Generic('JoinColumn', new Description('(name="column_id", referencedColumnName="id")'));
        $tag2 = new Generic('JoinColumn', new Description('(name="column_id_2", referencedColumnName="id")'));

        $tags = [
            $tag1,
            $tag2,
        ];

        $fixture  = new Description($body, $tags);
        $expected = '@JoinTable(name="table", joinColumns={@JoinColumn (name="column_id", referencedColumnName="id")}, '
        . 'inverseJoinColumns={@JoinColumn (name="column_id_2", referencedColumnName="id")})';
        $this->assertSame($expected, (string) $fixture);
    }
}
