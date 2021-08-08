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
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\This;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Method
 * @covers ::<private>
 */
class MethodTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Method('myMethod');

        $this->assertSame('method', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::isStatic
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()],
        ];

        $fixture = new Method(
            'myMethod',
            $arguments,
            new Void_(),
            true,
            new Description('My Description')
        );

        $this->assertSame(
            '@method static void myMethod(string $argument1, object $argument2) My Description',
            $fixture->render()
        );
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Method('myMethod');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getMethodName
     */
    public function testHasMethodName(): void
    {
        $expected = 'myMethod';

        $fixture = new Method($expected);

        $this->assertSame($expected, $fixture->getMethodName());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testHasArguments(): void
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertSame($arguments, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testArgumentsMayBePassedAsString(): void
    {
        $arguments = ['argument1'];
        $expected  = [
            ['name' => $arguments[0], 'type' => new Mixed_()],
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testArgumentTypeCanBeInferredAsMixed(): void
    {
        $arguments = [['name' => 'argument1']];
        $expected  = [
            ['name' => $arguments[0]['name'], 'type' => new Mixed_()],
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::getArguments
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::create
     */
    public function testRestArgumentIsParsedAsRegularArg(): void
    {
        $expected = [
            ['name' => 'arg1', 'type' => new Mixed_()],
            ['name' => 'rest', 'type' => new Mixed_()],
            ['name' => 'rest2', 'type' => new Array_()],
        ];

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');
        $description        = new Description('');
        $descriptionFactory->shouldReceive('create')->with('', $context)->andReturn($description);

        $fixture = Method::create(
            'void myMethod($arg1, ...$rest, array ... $rest2)',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertEquals($expected, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getReturnType
     */
    public function testHasReturnType(): void
    {
        $expected = new String_();

        $fixture = new Method('myMethod', [], $expected);

        $this->assertSame($expected, $fixture->getReturnType());
    }

    /**
     * @covers ::__construct
     * @covers ::getReturnType
     */
    public function testReturnTypeCanBeInferredAsVoid(): void
    {
        $fixture = new Method('myMethod', []);

        $this->assertEquals(new Void_(), $fixture->getReturnType());
    }

    /**
     * @covers ::__construct
     * @covers ::isStatic
     */
    public function testMethodCanBeStatic(): void
    {
        $expected = false;
        $fixture  = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());

        $expected = true;
        $fixture  = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());
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

        $fixture = new Method('myMethod', [], null, false, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::isStatic
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()],
        ];
        $fixture   = new Method('myMethod', $arguments, new Void_(), true, new Description('My Description'));

        $this->assertSame(
            'static void myMethod(string $argument1, object $argument2) My Description',
            (string) $fixture
        );
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::isStatic
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Method('myMethod', [], null, false, new Description(''));

        $this->assertSame(
            'void myMethod()',
            (string) $fixture
        );

        // ---

        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()],
        ];
        $fixture   = new Method('myMethod', $arguments, new Void_(), true);

        $this->assertSame(
            'static void myMethod(string $argument1, object $argument2)',
            (string) $fixture
        );
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethod(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description       = new Description('My Description');
        $expectedArguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Mixed_()],
        ];

        $descriptionFactory->shouldReceive('create')
            ->with('My Description', $context)
            ->andReturn($description);

        $fixture = Method::create(
            'static void myMethod(string $argument1, $argument2) My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame(
            'static void myMethod(string $argument1, mixed $argument2) My Description',
            (string) $fixture
        );
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals($expectedArguments, $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testReturnTypeThis(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('');

        $descriptionFactory->shouldReceive('create')->with('', $context)->andReturn($description);

        $fixture = Method::create(
            'static $this myMethod()',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertTrue($fixture->isStatic());
        $this->assertSame('static $this myMethod()', (string) $fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertInstanceOf(This::class, $fixture->getReturnType());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testReturnTypeNoneWithLongMethodName(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('');

        $descriptionFactory->shouldReceive('create')->with('', $context)->andReturn($description);

        $fixture = Method::create(
            'myVeryLongMethodName($node)',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertFalse($fixture->isStatic());
        $this->assertSame('void myVeryLongMethodName(mixed $node)', (string) $fixture);
        $this->assertSame('myVeryLongMethodName', $fixture->getMethodName());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
    }

    /**
     * @return string[][]
     */
    public function collectionReturnTypesProvider(): array
    {
        return [
            ['int[]', Array_::class, Integer::class, Compound::class],
            ['int[][]', Array_::class, Array_::class, Compound::class],
            ['Object[]', Array_::class, Object_::class, Compound::class],
            ['array[]', Array_::class, Array_::class, Compound::class],
        ];
    }

    /**
     * @uses         \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses         \phpDocumentor\Reflection\DocBlock\Description
     * @uses         \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses         \phpDocumentor\Reflection\TypeResolver
     * @uses         \phpDocumentor\Reflection\Types\Array_
     * @uses         \phpDocumentor\Reflection\Types\Compound
     * @uses         \phpDocumentor\Reflection\Types\Integer
     * @uses         \phpDocumentor\Reflection\Types\Object_
     *
     * @dataProvider collectionReturnTypesProvider
     * @covers ::create
     */
    public function testCollectionReturnTypes(
        string $returnType,
        string $expectedType,
        ?string $expectedValueType = null,
        ?string $expectedKeyType = null
    ): void {
        $resolver           = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')
            ->with('', null)
            ->andReturn(new Description(''));

        $fixture    = Method::create("${returnType} myMethod(\$arg)", $resolver, $descriptionFactory);
        $returnType = $fixture->getReturnType();
        $this->assertInstanceOf($expectedType, $returnType);

        if (!($returnType instanceof Array_)) {
            return;
        }

        $this->assertInstanceOf($expectedValueType, $returnType->getValueType());
        $this->assertInstanceOf($expectedKeyType, $returnType->getKeyType());
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfBodyIsEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        Method::create('');
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodReturnsNullIfBodyIsIncorrect(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->assertNull(Method::create('body('));
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfResolverIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Method::create('body');
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Method::create('body', new TypeResolver());
    }

    /**
     * @covers ::__construct
     */
    public function testCreationFailsIfBodyIsEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        new Method('');
    }

    /**
     * @covers ::__construct
     */
    public function testCreationFailsIfArgumentRecordContainsInvalidEntry(): void
    {
        $this->expectException('InvalidArgumentException');
        new Method('body', [['name' => 'myName', 'unknown' => 'nah']]);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testCreateMethodParenthesisMissing(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with(
            'My Description',
            $context
        )->andReturn($description);

        $fixture = Method::create(
            'static void myMethod My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('static void myMethod() My Description', (string) $fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals([], $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testCreateMethodEmptyArguments(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')
            ->with('My Description', $context)
            ->andReturn($description);

        $fixture = Method::create(
            'static void myMethod() My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('static void myMethod() My Description', (string) $fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals([], $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     * @uses \phpDocumentor\Reflection\Types\Void_
     *
     * @covers ::create
     */
    public function testCreateWithoutReturnType(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description = new Description('');

        $descriptionFactory->shouldReceive('create')->with('', $context)->andReturn($description);

        $fixture = Method::create(
            'myMethod()',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('void myMethod()', (string) $fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals([], $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     * @uses \phpDocumentor\Reflection\Types\Array_
     * @uses \phpDocumentor\Reflection\Types\Compound
     * @uses \phpDocumentor\Reflection\Types\Integer
     * @uses \phpDocumentor\Reflection\Types\Object_
     *
     * @covers ::create
     */
    public function testCreateWithMixedReturnTypes(): void
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $descriptionFactory->shouldReceive('create')->andReturn(new Description(''));

        $fixture = Method::create(
            'MyClass[]|int[] myMethod()',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('\MyClass[]|int[] myMethod()', (string) $fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals([], $fixture->getArguments());

        $this->assertEquals(
            new Compound([
                new Array_(new Object_(new Fqsen('\MyClass'))),
                new Array_(new Integer()),
            ]),
            $fixture->getReturnType()
        );
    }
}
