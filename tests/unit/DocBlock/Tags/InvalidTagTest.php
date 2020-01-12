<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\InvalidTag
 * @covers ::<private>
 * @covers ::getName
 * @covers ::render
 * @covers ::getException
 * @covers ::create
 */
final class InvalidTagTest extends TestCase
{
    public function testCreationWithoutError() : void
    {
        $tag = InvalidTag::create('Body', 'name');

        self::assertSame('name', $tag->getName());
        self::assertSame('@name Body', $tag->render());
        self::assertNull($tag->getException());
    }

    /**
     * @covers ::withError
     */
    public function testCreationWithError() : void
    {
        $exception = new Exception();
        $tag       = InvalidTag::create('Body', 'name')->withError($exception);

        self::assertSame('name', $tag->getName());
        self::assertSame('@name Body', $tag->render());
        self::assertSame($exception, $tag->getException());
    }
}
