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
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function str_replace;

use const PHP_EOL;

/**
 * @uses               \Webmozart\Assert\Assert
 * @uses               \phpDocumentor\Reflection\DocBlock
 *
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlockFactory
 * @covers             ::<private>
 */
class DocBlockFactoryTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     *
     * @covers ::__construct
     * @covers ::createInstance
     */
    public function testCreateFactoryUsingFactoryMethod(): void
    {
        $fixture = DocBlockFactory::createInstance();

        $this->assertInstanceOf(DocBlockFactory::class, $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateDocBlockFromReflection(): void
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docBlock       = '/** This is a DocBlock */';
        $classReflector = m::mock(ReflectionClass::class);
        $classReflector->shouldReceive('getDocComment')->andReturn($docBlock);
        $docblock = $fixture->create($classReflector);

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreateDocBlockFromStringWithDocComment(): void
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docblock = $fixture->create('/** This is a DocBlock */');

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::create
     * @covers ::__construct
     */
    public function testCreateDocBlockFromStringWithoutDocComment(): void
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docblock = $fixture->create('This is a DocBlock');

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     *
     * @dataProvider provideSummaryAndDescriptions
     */
    public function testSummaryAndDescriptionAreSeparated(string $given, string $summary, string $description): void
    {
        $tagFactory = m::mock(TagFactory::class);
        $fixture    = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $docblock = $fixture->create($given);

        $this->assertSame($summary, $docblock->getSummary());
        $this->assertEquals(new Description(str_replace(PHP_EOL, "\n", $description)), $docblock->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testDescriptionsRetainFormatting(): void
    {
        $tagFactory = m::mock(TagFactory::class);
        $fixture    = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $given = <<<DOCBLOCK
/**
 * This is a summary.
 * This is a multiline Description
 * that contains a code block.
 *
 *     See here: a CodeBlock
 */
DOCBLOCK;

        $description = "This is a multiline Description\nthat contains a code block.\n\n    See here: a CodeBlock";

        $docblock = $fixture->create($given);

        $this->assertEquals(new Description($description), $docblock->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testTagsAreInterpretedUsingFactory(): void
    {
        $tag        = m::mock(Tag::class);
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->with(m::any(), m::type(Context::class))->andReturn($tag);

        $fixture = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $given = <<<DOCBLOCK
/**
 * This is a summary.
 *
 * @author Mike van Riel <me@mikevanriel.com> This is with
 *   multiline description.
 */
DOCBLOCK;

        $docblock = $fixture->create($given, new Context(''));

        $this->assertEquals([$tag], $docblock->getTags());
    }

    /**
     * @return string[]
     */
    public function provideSummaryAndDescriptions(): array
    {
        return [
            ['This is a DocBlock', 'This is a DocBlock', ''],
            [
                'This is a DocBlock. This should still be summary.',
                'This is a DocBlock. This should still be summary.',
                '',
            ],
            [
                <<<DOCBLOCK
This is a DocBlock.
This should be a Description.
DOCBLOCK
,
                'This is a DocBlock.',
                'This should be a Description.',
            ],
            [
                <<<DOCBLOCK
This is a
multiline Summary.
This should be a Description.
DOCBLOCK
,
                "This is a\nmultiline Summary.",
                'This should be a Description.',
            ],
            [
                <<<DOCBLOCK
This is a Summary without dot but with a whiteline

This should be a Description.
DOCBLOCK
,
                'This is a Summary without dot but with a whiteline',
                'This should be a Description.',
            ],
            [
                <<<DOCBLOCK
This is a Summary with dot and with a whiteline.

This should be a Description.
DOCBLOCK
,
                'This is a Summary with dot and with a whiteline.',
                'This should be a Description.',
            ],
        ];
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\Types\Context
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testTagsWithContextNamespace(): void
    {
        $tagFactoryMock = m::mock(TagFactory::class);
        $fixture        = new DocBlockFactory(m::mock(DescriptionFactory::class), $tagFactoryMock);
        $context        = new Context('MyNamespace');

        $tagFactoryMock->shouldReceive('create')->with(m::any(), $context)->andReturn(new Param('param'));
        $docblock = $fixture->create('/** @param MyType $param */', $context);

        $this->assertInstanceOf(DocBlock::class, $docblock);
    }
}
