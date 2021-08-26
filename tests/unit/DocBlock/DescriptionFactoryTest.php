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

use Exception;
use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use phpDocumentor\Reflection\DocBlock\Tags\Link as LinkTag;
use phpDocumentor\Reflection\Types\Context;
use PHPUnit\Framework\TestCase;

use function str_replace;

use const PHP_EOL;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\DescriptionFactory
 * @covers ::<private>
 */
class DescriptionFactoryTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     * @dataProvider provideSimpleExampleDescriptions
     */
    public function testDescriptionCanParseASimpleString(string $contents): void
    {
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->never();

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, new Context(''));

        $this->assertSame($contents, $description->render());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     * @dataProvider provideEscapeSequences
     */
    public function testEscapeSequences(string $contents, string $expected): void
    {
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->never();

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, new Context(''));

        $this->assertSame($expected, $description->render());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Link
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testDescriptionCanParseAStringWithInlineTag(): void
    {
        $contents   = 'This is text for a {@link http://phpdoc.org/ description} that uses an inline tag.';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->once()
            ->with('@link http://phpdoc.org/ description', $context)
            ->andReturn(new LinkTag('http://phpdoc.org/', new Description('description')));

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Link
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testDescriptionCanParseAStringStartingWithInlineTag(): void
    {
        $contents   = '{@link http://phpdoc.org/ This} is text for a description that starts with an inline tag.';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->once()
            ->with('@link http://phpdoc.org/ This', $context)
            ->andReturn(new LinkTag('http://phpdoc.org/', new Description('This')));

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Link
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testDescriptionCanParseAStringContainingMultipleTags(): void
    {
        $contents   = 'This description has a {@link http://phpdoc.org/ This} another {@link http://phpdoc.org/ This2}';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->twice()
            ->andReturnValues(
                [
                    new LinkTag('http://phpdoc.org/', new Description('This')),
                    new LinkTag('http://phpdoc.org/', new Description('This2')),
                ]
            );

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
        $this->assertSame('This description has a %1$s another %2$s', $description->getBodyTemplate());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testIfSuperfluousStartingSpacesAreRemoved(): void
    {
        $factory         = new DescriptionFactory(m::mock(TagFactory::class));
        $descriptionText = <<<DESCRIPTION
This is a multiline
  description that you commonly
  see with tags.

      It does have a multiline code sample
      that should align, no matter what

  All spaces superfluous spaces on the
  second and later lines should be
  removed but the code sample should
  still be indented.
DESCRIPTION;

        $expectedDescription = <<<DESCRIPTION
This is a multiline
description that you commonly
see with tags.

    It does have a multiline code sample
    that should align, no matter what

All spaces superfluous spaces on the
second and later lines should be
removed but the code sample should
still be indented.
DESCRIPTION;

        $description = $factory->create($descriptionText, new Context(''));

        $this->assertSame(str_replace(PHP_EOL, "\n", $expectedDescription), $description->render());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\InvalidTag
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::__construct
     * @covers ::create
     */
    public function testDescriptionWithBrokenInlineTags(): void
    {
        $contents   = 'This {@see $name} is a broken use case, but used in real life.';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->once()
            ->with('@see $name', $context)
            ->andReturn(InvalidTag::create('$name', 'see', new Exception()));

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
    }

    /**
     * Provides a series of example strings that the parser should correctly interpret and return.
     *
     * @return string[][]
     */
    public function provideSimpleExampleDescriptions(): array
    {
        return [
            ['This is text for a description.'],
            ['This is text for a description containing { that is literal.'],
            ['This is text for a description containing } that is literal.'],
            ['This is text for a description with {just a text} that is not a tag.'],
        ];
    }

    /**
     * @return string[][]
     */
    public function provideEscapeSequences(): array
    {
        return [
            ['This is text for a description with a {@}.', 'This is text for a description with a @.'],
            ['This is text for a description with a {}.', 'This is text for a description with a }.'],
        ];
    }
}
