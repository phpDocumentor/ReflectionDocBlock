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

namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

/**
 * @uses \Webmozart\Assert\Assert
 *
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock
 * @covers ::<private>
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
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::getSummary
     */
    public function testDocBlockCanHaveASummary(): void
    {
        $summary = 'This is a summary';

        $fixture = new DocBlock($summary);

        $this->assertSame($summary, $fixture->getSummary());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::getSummary
     */
    public function testDocBlockCanHaveEllipsisInSummary(): void
    {
        $summary = 'This is a short (...) description.';

        $fixture = new DocBlock($summary);

        $this->assertSame($summary, $fixture->getSummary());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::getDescription
     */
    public function testDocBlockCanHaveADescription(): void
    {
        $description = new DocBlock\Description('');

        $fixture = new DocBlock('', $description);

        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     *
     * @covers ::__construct
     * @covers ::getTags
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
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     *
     * @covers ::__construct
     * @covers ::getTags
     */
    public function testDocBlockAllowsOnlyTags(): void
    {
        $this->expectException('InvalidArgumentException');
        $tags    = [null];
        $fixture = new DocBlock('', null, $tags);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     *
     * @covers ::__construct
     * @covers ::getTagsByName
     */
    public function testFindTagsInDocBlockByName(): void
    {
        $tag1 = m::mock(DocBlock\Tag::class);
        $tag2 = m::mock(DocBlock\Tag::class);
        $tag3 = m::mock(DocBlock\Tag::class);
        $tag4 = m::mock(DocBlock\Tag::class);
        $tags = [$tag1, $tag2, $tag3, $tag4];

        $tag1->shouldReceive('getName')->andReturn('abc');
        $tag2->shouldReceive('getName')->andReturn('abcd');
        $tag4->shouldReceive('getName')->andReturn('abcd');
        $tag3->shouldReceive('getName')->andReturn('ab');

        $fixture = new DocBlock('', null, $tags);

        $this->assertSame([$tag2, $tag4], $fixture->getTagsByName('abcd'));
        $this->assertSame([], $fixture->getTagsByName('Ebcd'));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     *
     * @covers ::__construct
     * @covers ::getTagsWithTypeByName
     */
    public function testFindTagsWithTypeInDocBlockByName(): void
    {
        $tag1 = new DocBlock\Tags\Var_('foo', new String_());
        $tag2 = new DocBlock\Tags\Var_('bar', new String_());
        $tag3 = new DocBlock\Tags\Return_(new String_());
        $tag4 = new DocBlock\Tags\Author('lall', '');

        $fixture = new DocBlock('', null, [$tag1, $tag2, $tag3, $tag4]);

        $this->assertSame([$tag1, $tag2], $fixture->getTagsWithTypeByName('var'));
        $this->assertSame([$tag3], $fixture->getTagsWithTypeByName('return'));
        $this->assertSame([], $fixture->getTagsWithTypeByName('author'));
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock::getTags
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tag
     *
     * @covers ::__construct
     * @covers ::hasTag
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
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::__construct
     * @covers ::getContext
     */
    public function testDocBlockKnowsInWhichNamespaceItIsAndWhichAliasesThereAre(): void
    {
        $context = new Context('');

        $fixture = new DocBlock('', null, [], $context);

        $this->assertSame($context, $fixture->getContext());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Location
     *
     * @covers ::__construct
     * @covers ::getLocation
     */
    public function testDocBlockKnowsAtWhichLineItIs(): void
    {
        $location = new Location(10);

        $fixture = new DocBlock('', null, [], null, $location);

        $this->assertSame($location, $fixture->getLocation());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::isTemplateStart
     * @covers ::isTemplateEnd
     */
    public function testDocBlockIsNotATemplateByDefault(): void
    {
        $fixture = new DocBlock('', null, [], null, null);

        $this->assertFalse($fixture->isTemplateStart());
        $this->assertFalse($fixture->isTemplateEnd());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::isTemplateStart
     */
    public function testDocBlockKnowsIfItIsTheStartOfADocBlockTemplate(): void
    {
        $fixture = new DocBlock('', null, [], null, null, true);

        $this->assertTrue($fixture->isTemplateStart());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::isTemplateEnd
     */
    public function testDocBlockKnowsIfItIsTheEndOfADocBlockTemplate(): void
    {
        $fixture = new DocBlock('', null, [], null, null, false, true);

        $this->assertTrue($fixture->isTemplateEnd());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Deprecated
     *
     * @covers ::__construct
     * @covers ::removeTag
     */
    public function testRemoveTag(): void
    {
        $someTag    = new Deprecated();
        $anotherTag = new Deprecated();

        $fixture = new DocBlock('', null, [$someTag]);

        $this->assertCount(1, $fixture->getTags());

        $fixture->removeTag($anotherTag);

        $this->assertCount(1, $fixture->getTags());

        $fixture->removeTag($someTag);

        $this->assertCount(0, $fixture->getTags());
    }
}
