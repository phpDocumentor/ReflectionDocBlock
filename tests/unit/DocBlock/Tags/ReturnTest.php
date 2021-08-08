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

use InvalidArgumentException;
use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Return_
 * @covers ::<private>
 */
class ReturnTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Return_::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Return_(new String_(), new Description('Description'));

        $this->assertSame('return', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Return_::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Return_::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Return_(new String_(), new Description('Description'));

        $this->assertSame('@return string Description', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Return_::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Return_(new String_(), new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getType
     */
    public function testHasType(): void
    {
        $expected = new String_();

        $fixture = new Return_($expected);

        $this->assertSame($expected, $fixture->getType());
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

        $fixture = new Return_(new String_(), $expected);

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
        $fixture = new Return_(new String_(), new Description('Description'));

        $this->assertSame('string Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Return_(new String_());

        $this->assertSame('string', (string) $fixture);

        // ---

        $fixture = new Return_(new String_(), new Description(''));

        $this->assertSame('string', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Return_::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\String_
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethod(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $type        = new String_();
        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Return_::create('string My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('string My Description', (string) $fixture);
        $this->assertEquals($type, $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * This test checks whether a braces in a Type are allowed.
     *
     * The advent of generics poses a few issues, one of them is that spaces can now be part of a type. In the past we
     * could purely rely on spaces to split the individual parts of the body of a tag; but when there is a type in play
     * we now need to check for braces.
     *
     * This test tests whether an error occurs demonstrating that the braces were taken into account; this test is still
     * expected to produce an exception because the TypeResolver does not support generics.
     *
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Return_::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\String_
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithGenericWithSpace(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')
            ->with('My Description', $context)
            ->andReturn($description);

        $fixture = Return_::create('array<string, string> My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('array<string,string> My Description', (string) $fixture);
        $this->assertEquals('array<string,string>', $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @see  self::testFactoryMethodWithGenericWithSpace()
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Return_::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\String_
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithGenericWithSpaceAndAddedEmojisToVerifyMultiByteBehaviour(): void
    {
        $this->markTestSkipped('A bug in the TypeResolver breaks this test');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"\array😁<string,😁 😁string>" is not a valid Fqsen.');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')
            ->with('My Description', $context)
            ->andReturn($description);

        Return_::create('array😁<string,😁 😁string> My Description', $resolver, $descriptionFactory, $context);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Return_::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\String_
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithEmojisToVerifyMultiByteBehaviour(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')
            ->with('My Description', $context)
            ->andReturn($description);

        $fixture = Return_::create('\My😁Class My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('\My😁Class My Description', (string) $fixture);
        $this->assertEquals('\My😁Class', $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfBodyIsNotEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->assertNull(Return_::create(''));
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfResolverIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Return_::create('body');
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Return_::create('body', new TypeResolver());
    }
}
