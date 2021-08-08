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
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Link
 * @covers ::<private>
 */
class LinkTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Link::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('link', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Link::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Link::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('@link http://this.is.my/link Description', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Link::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getLink
     */
    public function testHasLinkUrl(): void
    {
        $expected = 'http://this.is.my/link';

        $fixture = new Link($expected);

        $this->assertSame($expected, $fixture->getLink());
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

        $fixture = new Link('http://this.is.my/link', $expected);

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
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('http://this.is.my/link Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Link('http://this.is.my/link');

        $this->assertSame('http://this.is.my/link', (string) $fixture);

        // ---

        $fixture = new Link('http://this.is.my/link', new Description(''));

        $this->assertSame('http://this.is.my/link', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Link::<public>
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

        $links       = 'http://this.is.my/link';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Link::create('http://this.is.my/link My Description', $descriptionFactory, $context);

        $this->assertSame('http://this.is.my/link My Description', (string) $fixture);
        $this->assertSame($links, $fixture->getLink());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\FqsenResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Url
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithoutSpaceBeforeUrl(): void
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = Link::create(
            'http://this.is.my/link My Description ',
            $descriptionFactory,
            $context
        );

        $this->assertSame('http://this.is.my/link My Description ', (string) $fixture);
        $this->assertSame('My Description ', $fixture->getDescription() . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\FqsenResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Url
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithSpaceBeforeUrl(): void
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = Link::create(
            ' http://this.is.my/link My Description ',
            $descriptionFactory,
            $context
        );

        $this->assertSame('http://this.is.my/link My Description ', (string) $fixture);
        $this->assertSame('http://this.is.my/link My Description ', $fixture->getDescription() . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Link::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodCreatesEmptyLinkTag(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->never();

        $fixture = Link::create('', $descriptionFactory, new Context(''));

        $this->assertSame('', (string) $fixture);
        $this->assertSame('', $fixture->getLink());
        $this->assertSame(null, $fixture->getDescription());
    }
}
