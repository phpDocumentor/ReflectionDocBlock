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

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass phpDocumentor\Reflection\DocBlock\StandardTagFactory
 * @covers ::<private>
 */
class StandardTagFactoryTest extends TestCase
{

    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Generic
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses phpDocumentor\Reflection\DocBlock\Description
     */
    public function testCreatingAGenericTag()
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

        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        /** @var Generic $tag */
        $tag = $tagFactory->create('@' . $expectedTagName . ' This is a description', $context);

        $this->assertInstanceOf(Generic::class, $tag);
        $this->assertSame($expectedTagName, $tag->getName());
        $this->assertSame($expectedDescription, $tag->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testCreatingASpecificTag()
    {
        $context    = new Context('');
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));

        /** @var Author $tag */
        $tag = $tagFactory->create('@author Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\See
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen
     */
    public function testAnEmptyContextIsCreatedIfNoneIsProvided()
    {
        $fqsen              = '\Tag';
        $resolver           = m::mock(FqsenResolver::class)
            ->shouldReceive('resolve')
            ->with('Tag', m::type(Context::class))
            ->andReturn(new Fqsen($fqsen))
            ->getMock();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldIgnoreMissing();

        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        /** @var See $tag */
        $tag = $tagFactory->create('@see Tag');

        $this->assertInstanceOf(See::class, $tag);
        $this->assertSame($fqsen, (string)$tag->getReference());
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Author
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testPassingYourOwnSetOfTagHandlers()
    {
        $context    = new Context('');
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class), ['user' => Author::class]);

        /** @var Author $tag */
        $tag = $tagFactory->create('@user Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    /**
     * @covers ::create
     * @uses                     phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses                     phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The tag "@user[myuser" does not seem to be wellformed, please check it for errors
     */
    public function testExceptionIsThrownIfProvidedTagIsNotWellformed()
    {
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->create('@user[myuser');
    }

    /**
     * @covers ::__construct
     * @covers ::addParameter
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     */
    public function testAddParameterToServiceLocator()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addParameter('myParam', 'myValue');

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, 'myParam' => 'myValue'],
            'serviceLocator',
            $tagFactory
        );
    }

    /**
     * @covers ::addService
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     */
    public function testAddServiceToServiceLocator()
    {
        $service = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($service);

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, PassthroughFormatter::class => $service],
            'serviceLocator',
            $tagFactory
        );
    }

    /**
     * @covers ::addService
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     */
    public function testInjectConcreteServiceForInterfaceToServiceLocator()
    {
        $interfaceName = Formatter::class;
        $service       = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($service, $interfaceName);

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, $interfaceName => $service],
            'serviceLocator',
            $tagFactory
        );
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::create
     * @uses phpDocumentor\Reflection\DocBlock\Tags\Author
     */
    public function testRegisteringAHandlerForANewTag()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', Author::class);

        // Assert by trying to create one
        $tag = $tagFactory->create('@my-tag Mike van Riel <me@mikevanriel.com>');
        $this->assertInstanceOf(Author::class, $tag);
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedTagNameIsNotAString()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler([], Author::class);
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedTagNameIsEmpty()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('', Author::class);
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedTagNameIsNamespaceButNotFullyQualified()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('Name\Spaced\Tag', Author::class);
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerIsNotAString()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', []);
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerIsEmpty()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', '');
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerIsNotAnExistingClassName()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', 'IDoNotExist');
    }

    /**
     * @covers ::registerTagHandler
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @expectedException \InvalidArgumentException
     */
    public function testHandlerRegistrationFailsIfProvidedHandlerDoesNotImplementTheTagInterface()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', 'stdClass');
    }

    /**
     * @covers ::create
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::__construct
     * @uses phpDocumentor\Reflection\DocBlock\StandardTagFactory::addService
     * @uses phpDocumentor\Reflection\Docblock\Description
     * @uses phpDocumentor\Reflection\Docblock\Tags\Return_
     * @uses phpDocumentor\Reflection\Docblock\Tags\BaseTag
     */
    public function testReturntagIsMappedCorrectly()
    {
        $context    = new Context('');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory
            ->shouldReceive('create')
            ->once()
            ->with('', $context)
            ->andReturn(new Description(''));

        $typeResolver = new TypeResolver();

        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);
        $tagFactory->addService($typeResolver, TypeResolver::class);

        /** @var Return_ $tag */
        $tag = $tagFactory->create('@return mixed', $context);

        $this->assertInstanceOf(Return_::class, $tag);
        $this->assertSame('return', $tag->getName());
    }
}
