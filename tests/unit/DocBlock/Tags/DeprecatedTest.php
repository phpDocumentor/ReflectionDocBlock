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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Deprecated
 * @covers ::<private>
 */
class DeprecatedTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Deprecated('1.0', new Description('Description'));

        $this->assertSame('deprecated', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Deprecated('1.0', new Description('Description'));

        $this->assertSame('@deprecated 1.0 Description', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Deprecated('1.0', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getVersion
     */
    public function testHasVersionNumber(): void
    {
        $expected = '1.0';

        $fixture = new Deprecated($expected);

        $this->assertSame($expected, $fixture->getVersion());
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

        $fixture = new Deprecated('1.0', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $fixture = new Deprecated('1.0', new Description('Description'));

        $this->assertSame('1.0 Description', (string) $fixture);

        // ---

        $fixture = new Deprecated(null, new Description('My Description'));

        $this->assertSame('My Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Deprecated(null, new Description(''));

        $this->assertSame('', (string) $fixture);

        // ---

        $fixture = new Deprecated('1.0', new Description(''));

        $this->assertSame('1.0', (string) $fixture);

        // ---

        $fixture = new Deprecated('1.0');

        $this->assertSame('1.0', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::<public>
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

        $version     = '1.0';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Deprecated::create('1.0 My Description', $descriptionFactory, $context);

        $this->assertSame('1.0 My Description', (string) $fixture);
        $this->assertSame($version, $fixture->getVersion());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodCreatesEmptyDeprecatedTag(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->never();

        $fixture = Deprecated::create('', $descriptionFactory, new Context(''));

        $this->assertSame('', (string) $fixture);
        $this->assertNull($fixture->getVersion());
        $this->assertNull($fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Deprecated::__construct
     *
     * @covers ::create
     */
    public function testFactoryMethodReturnsNullIfBodyDoesNotMatchRegex(): void
    {
        $this->assertEquals(new Deprecated(), Deprecated::create('dkhf<'));
    }
}
