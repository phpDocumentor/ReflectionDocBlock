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

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use Exception;
use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @uses               \phpDocumentor\Reflection\DocBlock\Tags\Factory\AbstractPHPStanFactory
 * @uses               \phpDocumentor\Reflection\DocBlock\Tags\InvalidTag
 *
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Factory\AbstractPHPStanFactory
 * @covers             ::<private>
 */
class AbstractPHPStanFactoryTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @covers ::create
     */
    public function testCreateReturnsTagFromSupportingFactory(): void
    {
        $tag = m::mock(Tag::class);
        $factory = m::mock(PHPStanFactory::class);
        $factory->shouldReceive('supports')->andReturn(true);
        $factory->shouldReceive('create')->andReturn($tag);

        $sut = new AbstractPHPStanFactory($factory);

        $result = $sut->create('@param string $param');

        self::assertSame($tag, $result);
    }

    /**
     * @covers ::create
     */
    public function testCreateReturnsInvalidTagWhenNoFactorySupports(): void
    {
        $factory = m::mock(PHPStanFactory::class);
        $factory->shouldReceive('supports')->andReturn(false);

        $sut = new AbstractPHPStanFactory($factory);

        $result = $sut->create('@unknown string $param');

        self::assertInstanceOf(InvalidTag::class, $result);
        self::assertEquals('unknown', $result->getName());
    }

    /**
     * @covers ::create
     */
    public function testCreateReturnsInvalidTagWithErrorOnFactoryRuntimeException(): void
    {
        $factory = m::mock(PHPStanFactory::class);
        $factory->shouldReceive('supports')->andReturn(true);
        $factory->shouldReceive('create')->andThrow(new RuntimeException('Factory error'));

        $sut = new AbstractPHPStanFactory($factory);

        $result = $sut->create('@param string $param');

        self::assertInstanceOf(InvalidTag::class, $result);
        self::assertInstanceOf(Exception::class, $result->getException());
        self::assertEquals('Factory error', $result->getException()->getMessage());
    }

    /**
     * @covers ::create
     */
    public function testCreateReturnsInvalidTagWithErrorOnFactoryParserException(): void
    {
        $exception = m::mock(ParserException::class);
        $exception->shouldReceive('getMessage')->andReturn('Parser error');

        $factory = m::mock(PHPStanFactory::class);
        $factory->shouldReceive('supports')->andReturn(true);
        $factory->shouldReceive('create')->andThrow($exception);

        $sut = new AbstractPHPStanFactory($factory);

        $result = $sut->create('@param string $param');

        self::assertInstanceOf(InvalidTag::class, $result);
        self::assertSame($exception, $result->getException());
    }
}
