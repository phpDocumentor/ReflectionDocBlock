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
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as TagsFqsen;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url as UrlRef;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\See
 * @covers ::<private>
 */
class SeeTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\See::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new See(new FqsenRef(new Fqsen('\DateTime')), new Description('Description'));

        $this->assertSame('see', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\See::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\See::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new See(new FqsenRef(new Fqsen('\DateTime')), new Description('Description'));

        $this->assertSame('@see \DateTime Description', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\See::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new See(new FqsenRef(new Fqsen('\DateTime')), new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers ::__construct
     * @covers ::getReference
     */
    public function testHasReferenceToFqsen(): void
    {
        $expected = new FqsenRef(new Fqsen('\DateTime'));

        $fixture = new See($expected);

        $this->assertSame($expected, $fixture->getReference());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     */
    public function testHasDescription(): void
    {
        $expected = new Description('Description');

        $fixture = new See(new FqsenRef(new Fqsen('\DateTime')), $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $fixture = new See(new FqsenRef(new Fqsen('\DateTime::format()')), new Description('Description'));

        $this->assertSame('\DateTime::format() Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses   \phpDocumentor\Reflection\Fqsen
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new See(new FqsenRef(new Fqsen('\DateTime::format()')));

        $this->assertSame('\DateTime::format()', (string) $fixture);

        // ---

        $fixture = new See(new FqsenRef(new Fqsen('\DateTime::format()')), new Description(''));

        $this->assertSame('\DateTime::format()', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\FqsenResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethod(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = m::mock(FqsenResolver::class);
        $context            = new Context('');

        $fqsen       = new Fqsen('\DateTime');
        $description = new Description('My Description');

        $descriptionFactory
            ->shouldReceive('create')->with('My Description', $context)->andReturn($description);
        $resolver->shouldReceive('resolve')->with('DateTime', $context)->andReturn($fqsen);

        $fixture = See::create('DateTime My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('\DateTime My Description', (string) $fixture);
        $this->assertInstanceOf(FqsenRef::class, $fixture->getReference());
        $this->assertSame((string) $fqsen, (string) $fixture->getReference());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\FqsenResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithNonClassFQSEN(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = m::mock(FqsenResolver::class);
        $context            = new Context('');

        $fqsen       = new Fqsen('\DateTime');
        $description = new Description('My Description');

        $descriptionFactory
            ->shouldReceive('create')->with('My Description', $context)->andReturn($description);
        $resolver->shouldReceive('resolve')->with('DateTime', $context)->andReturn($fqsen);

        $fixture = See::create('DateTime::createFromFormat() My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('\DateTime::createFromFormat() My Description', (string) $fixture);
        $this->assertInstanceOf(FqsenRef::class, $fixture->getReference());
        $this->assertSame('\DateTime::createFromFormat()', (string) $fixture->getReference());
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
    public function testFactoryMethodWithUrl(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = m::mock(FqsenResolver::class);
        $context            = new Context('');

        $description = new Description('My Description');

        $descriptionFactory
            ->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $resolver->shouldNotReceive('resolve');

        $fixture = See::create('https://test.org My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('https://test.org My Description', (string) $fixture);
        $this->assertInstanceOf(UrlRef::class, $fixture->getReference());
        $this->assertSame('https://test.org', (string) $fixture->getReference());
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
    public function testFactoryMethodWithoutUrl(): void
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = See::create(
            'Foo My Description ',
            $fqsenResolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('\Foo My Description ', (string) $fixture);
        $this->assertInstanceOf(TagsFqsen::class, $fixture->getReference());
        $this->assertSame('\Foo', (string) $fixture->getReference());
        $this->assertSame('My Description ', $fixture->getDescription() . '');
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfBodyIsNotEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->assertNull(See::create(''));
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfResolverIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        See::create('body');
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        See::create('body', new FqsenResolver());
    }
}
