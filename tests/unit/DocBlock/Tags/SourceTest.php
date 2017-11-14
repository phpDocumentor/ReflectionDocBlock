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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Source
 * @covers ::<private>
 */
class SourceTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Source::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Source(1, null, new Description('Description'));

        $this->assertSame('source', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Source::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Source::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Source(1, 10, new Description('Description'));
        $this->assertSame('@source 1 10 Description', $fixture->render());

        $fixture = new Source(1, null, new Description('Description'));
        $this->assertSame('@source 1 Description', $fixture->render());

        $fixture = new Source(1);
        $this->assertSame('@source 1', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Source::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Source(1);

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getStartingLine
     */
    public function testHasStartingLine()
    {
        $expected = 1;

        $fixture = new Source($expected);

        $this->assertSame($expected, $fixture->getStartingLine());
    }

    /**
     * @covers ::__construct
     * @covers ::getLineCount
     */
    public function testHasLineCount()
    {
        $expected = 2;

        $fixture = new Source(1, $expected);

        $this->assertSame($expected, $fixture->getLineCount());
    }

    /**
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Source('1', null, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\Types\String_
     */
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Source(1, 10, new Description('Description'));

        $this->assertSame('1 10 Description', (string)$fixture);
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Source::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     */
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Source::create('1 10 My Description', $descriptionFactory, $context);

        $this->assertSame('1 10 My Description', (string)$fixture);
        $this->assertSame(1, $fixture->getStartingLine());
        $this->assertSame(10, $fixture->getLineCount());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Source::<public>
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Source::create('', $descriptionFactory);
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Source::create([]);
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Source::create('1');
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfStartingLineIsNotInteger()
    {
        new Source('blabla');
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfLineCountIsNotIntegerOrNull()
    {
        new Source('1', []);
    }
}
