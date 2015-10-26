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

namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Types\Context;

/**
 * @coversDefaultClass phpDocumentor\Reflection\DocBlockFactory
 * @covers             ::<private>
 * @uses               \Webmozart\Assert\Assert
 * @uses               phpDocumentor\Reflection\DocBlock
 */
class DocBlockFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::createInstance
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     */
    public function testCreateFactoryUsingFactoryMethod()
    {
        $fixture = DocBlockFactory::createInstance();

        $this->assertInstanceOf(DocBlockFactory::class, $fixture);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses   phpDocumentor\Reflection\DocBlock\Description
     */
    public function testCreateDocBlockFromReflection()
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docBlock       = '/** This is a DocBlock */';
        $classReflector = m::mock(\ReflectionClass::class);
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
     * @covers ::__construct
     * @covers ::create
     * @uses   phpDocumentor\Reflection\DocBlock\Description
     */
    public function testCreateDocBlockFromStringWithDocComment()
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
     * @covers ::create
     * @covers ::__construct
     * @uses   phpDocumentor\Reflection\DocBlock\Description
     */
    public function testCreateDocBlockFromStringWithoutDocComment()
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
     * @covers ::__construct
     * @covers ::create
     * @uses         phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses         phpDocumentor\Reflection\DocBlock\Description
     * @dataProvider provideSummaryAndDescriptions
     */
    public function testSummaryAndDescriptionAreSeparated($given, $summary, $description)
    {
        $tagFactory = m::mock(TagFactory::class);
        $fixture    = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $docblock = $fixture->create($given);

        $this->assertSame($summary, $docblock->getSummary());
        $this->assertEquals(new Description($description), $docblock->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses phpDocumentor\Reflection\DocBlock\Description
     */
    public function testDescriptionsRetainFormatting()
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

        $description = <<<DESCRIPTION
This is a multiline Description
that contains a code block.

    See here: a CodeBlock
DESCRIPTION;

        $docblock = $fixture->create($given);

        $this->assertEquals(new Description($description), $docblock->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses phpDocumentor\Reflection\DocBlock\Description
     */
    public function testTagsAreInterpretedUsingFactory()
    {
        $tagString = <<<TAG
@author Mike van Riel <me@mikevanriel.com> This is with
  multiline description.
TAG;

        $tag        = m::mock(Tag::class);
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->with($tagString, m::type(Context::class))->andReturn($tag);

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

    public function provideSummaryAndDescriptions()
    {
        return [
            ['This is a DocBlock', 'This is a DocBlock', ''],
            [
                'This is a DocBlock. This should still be summary.',
                'This is a DocBlock. This should still be summary.',
                ''
            ],
            [
                <<<DOCBLOCK
This is a DocBlock.
This should be a Description.
DOCBLOCK
                ,
                'This is a DocBlock.',
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a
multiline Summary.
This should be a Description.
DOCBLOCK
                ,
                "This is a\nmultiline Summary.",
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a Summary without dot but with a whiteline

This should be a Description.
DOCBLOCK
                ,
                'This is a Summary without dot but with a whiteline',
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a Summary with dot and with a whiteline.

This should be a Description.
DOCBLOCK
                ,
                'This is a Summary with dot and with a whiteline.',
                'This should be a Description.'
            ],
        ];
    }

    /**
     * @covers ::__construct
     * @covers ::create
     *
     * @uses   phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses   phpDocumentor\Reflection\DocBlock\Description
     * @uses   phpDocumentor\Reflection\Types\Context
     * @uses   phpDocumentor\Reflection\DocBlock\Tags\Param
     */
    public function testTagsWithContextNamespace()
    {
        $tagFactoryMock = m::mock(TagFactory::class);
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), $tagFactoryMock);
        $context = new Context('MyNamespace');

        $tagFactoryMock->shouldReceive('create')->with(m::any(), $context)->andReturn(new Param('param'));
        $docblock = $fixture->create('/** @param MyType $param */', $context);
    }

    /**
     * @covers ::__construct
     * @covers ::create
     *
     * @uses phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses phpDocumentor\Reflection\DocBlock\Description
     */
    public function testTagsAreFilteredForNullValues()
    {
        $tagString = <<<TAG
@author Mike van Riel <me@mikevanriel.com> This is with
  multiline description.
TAG;

        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->with($tagString, m::any())->andReturn(null);

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

        $this->assertEquals([], $docblock->getTags());
    }
}
