<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @generic      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Generic
 * @covers ::<private>
 */
class GenericTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Generic::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('generic', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Generic::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Generic::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('@generic Description', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Generic::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Generic('generic', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     */
    public function testHasDescription(): void
    {
        $expected = new Description('Description');

        $fixture = new Generic('generic', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Generic('generic');

        $this->assertSame('', (string) $fixture);

        // ---

        $fixture = new Generic('generic', new Description(''));

        $this->assertSame('', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethod(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $generics    = 'generic';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Generic::create('My Description', 'generic', $descriptionFactory, $context);

        $this->assertSame('My Description', (string) $fixture);
        $this->assertSame($generics, $fixture->getName());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfNameIsNotEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        Generic::create('', '');
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function testFactoryMethodFailsIfNameContainsIllegalCharacters(): void
    {
        $this->expectException('InvalidArgumentException');
        Generic::create('', 'name/myname');
    }
}
