<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

use function fopen;
use function serialize;
use function unserialize;

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
    public function testCreationWithoutError(): void
    {
        $tag = InvalidTag::create('Body', 'name');

        self::assertSame('name', $tag->getName());
        self::assertSame('@name Body', $tag->render());
        self::assertNull($tag->getException());
    }

    /**
     * @covers ::withError
     * @covers ::__toString
     */
    public function testCreationWithError(): void
    {
        $exception = new Exception();
        $tag       = InvalidTag::create('Body', 'name')->withError($exception);

        self::assertSame('name', $tag->getName());
        self::assertSame('@name Body', $tag->render());
        self::assertSame('Body', (string) $tag);
        self::assertSame($exception, $tag->getException());
    }

    public function testCreationWithErrorContainingClosure(): void
    {
        try {
            $this->throwExceptionFromClosureWithClosureArgument();
        } catch (Throwable $e) {
            $parentException = new Exception('test', 0, $e);
            $tag             = InvalidTag::create('Body', 'name')->withError($parentException);
            self::assertSame('name', $tag->getName());
            self::assertSame('@name Body', $tag->render());
            self::assertSame($parentException, $tag->getException());
            $trace = $tag->getException()->getPrevious()->getTrace();

            if (isset($trace[0]['args'])) { // Not set by default on 7.4
                self::assertStringStartsWith('(Closure at', $trace[0]['args'][0]);
                self::assertStringContainsString(__FILE__, $trace[0]['args'][0]);
            }

            self::assertEquals($parentException, unserialize(serialize($parentException)));
        }
    }

    private function throwExceptionFromClosureWithClosureArgument(): void
    {
        $function = static function (): void {
            throw new InvalidArgumentException();
        };

        $function($function);
    }

    public function testCreationWithErrorContainingResource(): void
    {
        try {
            $this->throwExceptionWithResourceArgument();
        } catch (Throwable $e) {
            $parentException = new Exception('test', 0, $e);
            $tag             = InvalidTag::create('Body', 'name')->withError($parentException);
            self::assertSame('name', $tag->getName());
            self::assertSame('@name Body', $tag->render());
            self::assertSame($parentException, $tag->getException());
            $trace = $tag->getException()->getPrevious()->getTrace();

            if (isset($trace[0]['args'])) { // Not set by default on 7.4
                self::assertStringStartsWith(
                    'resource(stream)',
                    $trace[0]['args'][0]
                );
            }

            self::assertEquals($parentException, unserialize(serialize($parentException)));
        }
    }

    private function throwExceptionWithResourceArgument(): void
    {
        $function = static function (): void {
            throw new InvalidArgumentException();
        };

        $function(fopen(__FILE__, 'r'));
    }

    public function testCreationWithErrorFromEval(): void
    {
        $builder = static function (): InvalidArgumentException {
            return new InvalidArgumentException();
        };

        $exception = eval('return $builder();');
        $tag = InvalidTag::create('Body', 'name')->withError($exception);

        self::assertSame('name', $tag->getName());
        self::assertSame('@name Body', $tag->render());
        self::assertSame('Body', (string) $tag);
        self::assertSame($exception, $tag->getException());
    }
}
