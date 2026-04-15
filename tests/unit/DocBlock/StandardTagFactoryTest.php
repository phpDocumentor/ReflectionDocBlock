<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use InvalidArgumentException;
use Mockery as m;
use Name\Spaced\Tag;
use phpDocumentor\Reflection\Assets\CustomParam;
use phpDocumentor\Reflection\Assets\CustomServiceClass;
use phpDocumentor\Reflection\Assets\CustomServiceInterface;
use phpDocumentor\Reflection\Assets\CustomTagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use phpDocumentor\Reflection\DocBlock\Tags\Extends_;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Implements_;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\Mixin;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use phpDocumentor\Reflection\DocBlock\Tags\TemplateCovariant;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\StandardTagFactory
 * @covers ::<private>
 */
class StandardTagFactoryTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreatingAGenericTag(): void
    {
        $expectedTagName         = 'unknown-tag';
        $expectedDescriptionText = 'This is a description';
        $expectedDescription     = new Description($expectedDescriptionText);
        $context                 = new Context('');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory
            ->shouldReceive('create')
            ->once()
            ->with($expectedDescriptionText, $context)
            ->andReturn($expectedDescription);

        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        $tag = $tagFactory->create('@' . $expectedTagName . ' This is a description', $context);

        $this->assertInstanceOf(Generic::class, $tag);
        $this->assertSame($expectedTagName, $tag->getName());
        $this->assertSame($expectedDescription, $tag->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreatingAGenericTagWithDescriptionText(): void
    {
        $expectedTagName         = 'unknown-tag';
        $expectedDescriptionText = ' foo Bar 123 ';
        $context                 = new Context('');

        $tagFactory = StandardTagFactory::createInstance(new FqsenResolver());
        $tagFactory->addService(new DescriptionFactory($tagFactory), DescriptionFactory::class);

        $tag = $tagFactory->create('@' . $expectedTagName . $expectedDescriptionText, $context);

        $this->assertInstanceOf(Generic::class, $tag);
        $this->assertSame('foo Bar 123', $tag . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testCreatingASpecificTag(): void
    {
        $context    = new Context('');
        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));

        $tag = $tagFactory->create('@author Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Example
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testExampleTagIsRecognisedOutOfTheBox(): void
    {
        $context    = new Context('');
        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));

        $tag = $tagFactory->create('@example "path/to/example.php" 3 10 Example description.', $context);

        $this->assertInstanceOf(Example::class, $tag);
        $this->assertSame('example', $tag->getName());
        $this->assertSame('path/to/example.php', $tag->getFilePath());
        $this->assertSame(3, $tag->getStartingLine());
        $this->assertSame(10, $tag->getLineCount());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testAnEmptyContextIsCreatedIfNoneIsProvided(): void
    {
        $fqsen              = '\Tag';
        $resolver           = m::mock(FqsenResolver::class);
        $resolver->allows('resolve')
            ->with('Tag', m::type(Context::class))
            ->andReturns(new Fqsen($fqsen))
            ->getMock();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldIgnoreMissing();

        $tagFactory = StandardTagFactory::createInstance($resolver);
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        $tag = $tagFactory->create('@see Tag');

        $this->assertInstanceOf(See::class, $tag);
        $this->assertSame($fqsen, (string) $tag->getReference());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testPassingYourOwnSetOfTagHandlers(): void
    {
        $context    = new Context('');
        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));
        $tagFactory->registerTagHandler('user', Author::class);

        $tag = $tagFactory->create('@user Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testPassingYourOwnSetOfTagHandlersWithGermanChars(): void
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = StandardTagFactory::createInstance($fqsenResolver);
        $context            = new Context('');

        $tagFactory->registerTagHandler('my-täg', Author::class);

        $tag = $tagFactory->create('@my-täg foo bar ', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
        $this->assertSame('foo bar', $tag . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testPassingYourOwnSetOfTagHandlersWithoutComment(): void
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = StandardTagFactory::createInstance($fqsenResolver);
        $context            = new Context('');

        $tagFactory->registerTagHandler('my-täg', Author::class);
        $tag = $tagFactory->create('@my-täg', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    public function testTagWithHandlerObject(): void
    {
        $fqsenResolver = new FqsenResolver();

        $customFactory = new CustomTagFactory();
        $injectedClass = new CustomServiceClass();

        $tagFactory = StandardTagFactory::createInstance($fqsenResolver);
        $tagFactory->addService($injectedClass);
        $tagFactory->registerTagHandler('param', $customFactory);
        $tag = $tagFactory->create('@param foo');

        self::assertSame('custom', $tag->getName());
        self::assertSame($injectedClass, $customFactory->class);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testPassingYourOwnSetOfTagHandlersWithEmptyComment(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'The tag "@my-täg " does not seem to be wellformed, please check it for errors'
        );

        $fqsenResolver      = new FqsenResolver();
        $context            = new Context('');

        $tagFactory = StandardTagFactory::createInstance(
            $fqsenResolver,
        );

        $tagFactory->create('@my-täg ', $context);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::create
     */
    public function testExceptionIsThrownIfProvidedTagIsNotWellformed(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'The tag "@user[myuser" does not seem to be wellformed, please check it for errors'
        );
        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));
        $tagFactory->create('@user[myuser');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::__construct
     * @covers ::addParameter
     */
    public function testAddParameterToServiceLocator(): void
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);
        $tagFactory->registerTagHandler('spy', CustomParam::class);

        $tagFactory->addParameter('myParam', 'myValue');
        $spy = $tagFactory->create('@spy');

        self::assertInstanceOf(CustomParam::class, $spy);
        $this->assertSame($resolver, $spy->fqsenResolver);
        $this->assertSame('myValue', $spy->myParam);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     *
     * @covers ::addService
     */
    public function testAddServiceToServiceLocator(): void
    {
        $service = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);
        $tagFactory->addService($service);
        $tagFactory->registerTagHandler('spy', CustomServiceClass::class);

        $spy = $tagFactory->create('@spy');

        self::assertInstanceOf(CustomServiceClass::class, $spy);
        $this->assertSame($service, $spy->formatter);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     *
     * @covers ::addService
     */
    public function testInjectConcreteServiceForInterfaceToServiceLocator(): void
    {
        $interfaceName = Formatter::class;
        $service       = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);
        $tagFactory->addService($service, $interfaceName);
        $tagFactory->registerTagHandler('spy', CustomServiceInterface::class);

        $spy = $tagFactory->create('@spy');

        self::assertInstanceOf(CustomServiceInterface::class, $spy);
        $this->assertSame($service, $spy->formatter);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Author
     *
     * @covers ::registerTagHandler
     */
    public function testRegisteringAHandlerForANewTag(): void
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);

        $tagFactory->registerTagHandler('my-tag', Author::class);

        // Assert by trying to create one
        $tag = $tagFactory->create('@my-tag Mike van Riel <me@mikevanriel.com>');
        $this->assertInstanceOf(Author::class, $tag);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::registerTagHandler
     */
    public function testHandlerRegistrationFailsIfProvidedTagNameIsNamespaceButNotFullyQualified(): void
    {
        $this->expectException('InvalidArgumentException');
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);
        // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
        $tagFactory->registerTagHandler(
            Tag::class, // @phpstan-ignore class.notFound
            Author::class
        );
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::registerTagHandler
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerIsEmpty(): void
    {
        $this->expectException('InvalidArgumentException');
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);
        //@phpstan-ignore argument.type
        $tagFactory->registerTagHandler('my-tag', '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::registerTagHandler
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerIsNotAnExistingClassName(): void
    {
        $this->expectException('InvalidArgumentException');
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);

        //@phpstan-ignore argument.type
        $tagFactory->registerTagHandler('my-tag', 'IDoNotExist');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     *
     * @covers ::registerTagHandler
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerDoesNotImplementTheTagInterface(): void
    {
        $this->expectException('InvalidArgumentException');
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($resolver);

        //@phpstan-ignore argument.type
        $tagFactory->registerTagHandler('my-tag', 'stdClass');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\Docblock\Description
     * @uses \phpDocumentor\Reflection\Docblock\Tags\Return_
     * @uses \phpDocumentor\Reflection\Docblock\Tags\BaseTag
     *
     * @covers ::create
     */
    public function testReturnTagIsMappedCorrectly(): void
    {
        $context = new Context('');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory
            ->shouldReceive('create')
            ->once()
            ->with('mixed test', $context)
            ->andReturn(new Description(''));

        $typeResolver = new TypeResolver();

        $tagFactory = StandardTagFactory::createInstance(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);
        $tagFactory->addService($typeResolver, TypeResolver::class);

        $tag = $tagFactory->create('@deprecated mixed test', $context);

        $this->assertInstanceOf(Deprecated::class, $tag);
        $this->assertSame('deprecated', $tag->getName());
    }

    /**
     * @dataProvider validTagProvider
     */
    public function testValidFormattedTags(string $input, string $tagName, string $render): void
    {
        $fqsenResolver = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($fqsenResolver);
        $tagFactory->registerTagHandler('tag', Generic::class);
        $tag = $tagFactory->create($input);

        self::assertSame($tagName, $tag->getName());
        self::assertSame($render, $tag->render());
    }

    /**
     * @return string[][]
     * @phpstan-return array<string, array<int, string>>
     */
    public function validTagProvider(): array
    {
        //rendered result is adding a space, because the tags are not rendered properly.
        return [
            'tag without body' => [
                '@tag',
                'tag',
                '@tag',
            ],
            'tag specialization' => [
                '@tag:some-spec body',
                'tag:some-spec',
                '@tag:some-spec body',
            ],
            'tag specialization followed by parenthesis' => [
                '@tag:some-spec(body)',
                'tag:some-spec',
                '@tag:some-spec (body)',
            ],
            'tag with textual description' => [
                '@tag some text',
                'tag',
                '@tag some text',
            ],
            'tag body starting with square brackets is allowed' => [
                '@tag [is valid]',
                'tag',
                '@tag [is valid]',
            ],
            'tag body starting with curly brackets is allowed' => [
                '@tag {is valid}',
                'tag',
                '@tag {is valid}',
            ],
            'tag name followed by curly brackets directly is allowed' => [
                '@tag{is valid}',
                'tag',
                '@tag {is valid}',
            ],
            'parenthesis directly following a tag name is valid' => [
                '@tag(is valid)',
                'tag',
                '@tag (is valid)',
            ],
            'full-qualified-class-name following a tag name is valid' => [
                '@tag \InvalidArgumentException',
                'tag',
                '@tag \InvalidArgumentException',
            ],
        ];
    }

    /**
     * @dataProvider invalidTagProvider
     */
    public function testInValidFormattedTags(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        $fqsenResolver = m::mock(FqsenResolver::class);
        $tagFactory = StandardTagFactory::createInstance($fqsenResolver);
        $tagFactory->registerTagHandler('tag', Generic::class);
        $tagFactory->create($input);
    }

    /**
     * @return string[][]
     * @phpstan-return list<array<int, string>>
     */
    public function invalidTagProvider(): array
    {
        return [
            ['@tag[invalid]'],
            ['@tag@invalid'],
        ];
    }

    /**
     * @param class-string $expectedClass
     *
     * @dataProvider provideCreateWithTagWithTypesData
     */
    public function testCreateWithTagWithTypes(string $input, string $expectedClass): void
    {
        $tagFactory = StandardTagFactory::createInstance(new FqsenResolver());
        $tag = $tagFactory->create($input);

        $this->assertInstanceOf($expectedClass, $tag);
    }

    /**
     * @return list<array{string, class-string}>
     */
    public static function provideCreateWithTagWithTypesData(): array
    {
        return [
            ['@mixin Foo', Mixin::class],
            ['@method string do()', Method::class],
            ['@param Foo $bar', Param::class],
            ['@property-read Foo $bar', PropertyRead::class],
            ['@property Foo $bar', Property::class],
            ['@property-write Foo $bar', PropertyWrite::class],
            ['@return string', Return_::class],
            ['@throws Throwable', Throws::class],
            ['@var string $var', Var_::class],
            ['@template T', Template::class],
            ['@template-covariant T', TemplateCovariant::class],
            ['@extends Foo<Bar>', Extends_::class],
            ['@implements Foo<Bar>', Implements_::class],

            // TODO: add factories for this tags
            // ['@template-extends Foo', TemplateExtends::class],
            // ['@template-implements Foo', TemplateImplements::class],
        ];
    }
}
