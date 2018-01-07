<?php declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass phpDocumentor\Reflection\DocBlock
 * @covers ::<private>
 * @uses \Webmozart\Assert\Assert
 */
class DocBlockTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @covers ::__construct
     * @covers ::getSummary
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDocBlockCanHaveASummary(): void
    {
        $summary = 'This is a summary';

        $fixture = new DocBlock($summary);

        $this->assertSame($summary, $fixture->getSummary());
    }

    /**
     * @covers ::__construct
     * @covers ::getSummary
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDocBlockCanHaveEllipsisInSummary(): void
    {
        $summary = 'This is a short (...) description.';

        $fixture = new DocBlock($summary);

        $this->assertSame($summary, $fixture->getSummary());
    }

    /**
     * @covers ::__construct
     * @covers ::getDescription
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDocBlockCanHaveADescription(): void
    {
        $description = new DocBlock\Description('');

        $fixture = new DocBlock('', $description);

        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::getTags
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function testDocBlockCanHaveTags(): void
    {
        $tags = [
            m::mock(DocBlock\Tag::class),
        ];

        $fixture = new DocBlock('', null, $tags);

        $this->assertSame($tags, $fixture->getTags());
    }

    /**
     * @covers ::__construct
     * @covers ::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function testDocBlockAllowsOnlyTags(): void
    {
        $this->expectException('InvalidArgumentException');
        $tags = [
            null,
        ];
        $fixture = new DocBlock('', null, $tags);
    }

    /**
     * @covers ::__construct
     * @covers ::getTagsByName
     *
     * @uses \phpDocumentor\Reflection\DocBlock::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function testFindTagsInDocBlockByName(): void
    {
        $tag1 = m::mock(DocBlock\Tag::class);
        $tag2 = m::mock(DocBlock\Tag::class);
        $tag3 = m::mock(DocBlock\Tag::class);
        $tags = [$tag1, $tag2, $tag3];

        $tag1->shouldReceive('getName')->andReturn('abc');
        $tag2->shouldReceive('getName')->andReturn('abcd');
        $tag3->shouldReceive('getName')->andReturn('ab');

        $fixture = new DocBlock('', null, $tags);

        $this->assertSame([$tag2], $fixture->getTagsByName('abcd'));
        $this->assertSame([], $fixture->getTagsByName('Ebcd'));
    }

    /**
     * @covers ::__construct
     * @covers ::hasTag
     *
     * @uses \phpDocumentor\Reflection\DocBlock::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     */
    public function testCheckIfThereAreTagsWithAGivenName(): void
    {
        $tag1 = m::mock(DocBlock\Tag::class);
        $tag2 = m::mock(DocBlock\Tag::class);
        $tag3 = m::mock(DocBlock\Tag::class);
        $tags = [$tag1, $tag2, $tag3];

        $tag1->shouldReceive('getName')->twice()->andReturn('abc');
        $tag2->shouldReceive('getName')->twice()->andReturn('abcd');
        $tag3->shouldReceive('getName')->once();

        $fixture = new DocBlock('', null, $tags);

        $this->assertTrue($fixture->hasTag('abcd'));
        $this->assertFalse($fixture->hasTag('Ebcd'));
    }

    /**
     * @covers ::__construct
     * @covers ::getContext
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     */
    public function testDocBlockKnowsInWhichNamespaceItIsAndWhichAliasesThereAre(): void
    {
        $context = new Context('');

        $fixture = new DocBlock('', null, [], $context);

        $this->assertSame($context, $fixture->getContext());
    }

    /**
     * @covers ::__construct
     * @covers ::getLocation
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Location
     */
    public function testDocBlockKnowsAtWhichLineItIs(): void
    {
        $location = new Location(10);

        $fixture = new DocBlock('', null, [], null, $location);

        $this->assertSame($location, $fixture->getLocation());
    }

    /**
     * @covers ::__construct
     * @covers ::isTemplateStart
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDocBlockKnowsIfItIsTheStartOfADocBlockTemplate(): void
    {
        $fixture = new DocBlock('', null, [], null, null, true);

        $this->assertTrue($fixture->isTemplateStart());
    }

    /**
     * @covers ::__construct
     * @covers ::isTemplateEnd
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDocBlockKnowsIfItIsTheEndOfADocBlockTemplate(): void
    {
        $fixture = new DocBlock('', null, [], null, null, false, true);

        $this->assertTrue($fixture->isTemplateEnd());
    }

    /**
     * @covers ::__construct
     * @covers ::removeTag
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Deprecated
     */
    public function testRemoveTag(): void
    {
        $someTag = new Deprecated();
        $anotherTag = new Deprecated();

        $fixture = new DocBlock('', null, [$someTag]);

        $this->assertCount(1, $fixture->getTags());

        $fixture->removeTag($anotherTag);

        $this->assertCount(1, $fixture->getTags());

        $fixture->removeTag($someTag);

        $this->assertCount(0, $fixture->getTags());
    }
}
