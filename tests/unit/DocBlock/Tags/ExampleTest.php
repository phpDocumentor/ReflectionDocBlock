<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Example
 * @covers ::<private>
 */
class ExampleTest extends TestCase
{
    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::create
     * @covers ::__construct
     * @covers ::getContent
     */
    public function testExampleWithoutContent(): void
    {
        $tag = Example::create('"example1.php"');
        $this->assertEquals('"example1.php"', $tag->getContent());
        $this->assertEquals('', $tag->getDescription());
        $this->assertEquals('example', $tag->getName());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getDescription
     */
    public function testWithDescription(): void
    {
        $tag = Example::create('"example1.php" some text');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals('some text', $tag->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     */
    public function testStartlineIsParsed(): void
    {
        $tag = Example::create('"example1.php" 10');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getDescription
     */
    public function testAllowOmitingLineCount(): void
    {
        $tag = Example::create('"example1.php" 10 some text');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
        $this->assertEquals('some text', $tag->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getLineCount
     */
    public function testLengthIsParsed(): void
    {
        $tag = Example::create('"example1.php" 10 5');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
        $this->assertEquals(5, $tag->getLineCount());
    }

    /**
     * @covers ::create
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $tag = Example::create('"example1.php" 10 5 test text');

        $this->assertSame('"example1.php" 10 5 test text', (string) $tag);

        // ---

        $tag = Example::create('file://example1.php');

        $this->assertSame('file://example1.php', (string) $tag);

        // ---

        $tag = Example::create('0 foo bar');

        $this->assertSame('0 foo bar', (string) $tag);

        // ---

        $tag = Example::create('$redisCluster->pttl(\'key\');');

        $this->assertSame('$redisCluster->pttl(\'key\');', (string) $tag);

        // ---

        $tag = Example::create(' "example1.php" 10 5 test text ');

        $this->assertSame('"example1.php" 10 5 test text', (string) $tag);
    }

    /**
     * @covers ::create
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $tag = Example::create('');

        $this->assertSame('', (string) $tag);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     *
     * @dataProvider tagContentProvider
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getLineCount
     * @covers ::getDescription
     * @covers ::getContent
     */
    public function testFactoryMethod(
        string $input,
        string $filePath,
        int $startLine,
        int $lineCount,
        ?string $description,
        string $content
    ): void {
        $tag = Example::create($input);
        $this->assertSame($filePath, $tag->getFilePath());
        $this->assertSame($startLine, $tag->getStartingLine());
        $this->assertSame($lineCount, $tag->getLineCount());
        $this->assertSame($description, $tag->getDescription());
        $this->assertSame($content, $tag->getContent());
    }

    /** @return mixed[][] */
    public function tagContentProvider(): array
    {
        return [
            [
                '"example1.php" 10 5 test text ',
                'example1.php',
                10,
                5,
                'test text',
                'test text',
            ],
            [
                'example1.php 10 5 test text',
                'example1.php',
                10,
                5,
                'test text',
                'test text',
            ],
            [
                'example1.php 1 10 test text',
                'example1.php',
                1,
                10,
                'test text',
                'test text',
            ],
            [
                'example1.php',
                'example1.php',
                1,
                0,
                null,
                'example1.php',
            ],
            [
                'file://example1.php ',
                'file://example1.php',
                1,
                0,
                '',
                'file://example1.php',
            ],
            [
                '/example1.php',
                '/example1.php',
                1,
                0,
                null,
                '/example1.php',
            ],
        ];
    }

    /**
     * @dataProvider invalidExampleProvider
     * @covers ::__construct
     */
    public function testValidatesArguments(
        string $filePath,
        bool $isUrl,
        int $startLine,
        int $lineCount,
        string $description
    ): void {
        $this->expectException(InvalidArgumentException::class);

        new Example(
            $filePath,
            $isUrl,
            $startLine,
            $lineCount,
            $description
        );
    }

    /** @return mixed[][] */
    public function invalidExampleProvider(): array
    {
        return [
            'invalid start' => [
                '/some/path',
                false,
                -1,
                0,
                'text',
            ],
            'invalid start 2' => [
                '/some/path',
                false,
                -10,
                0,
                'text',
            ],
            'invalid length' => [
                '/some/path',
                false,
                1,
                -1,
                'text',
            ],
            'invalid length 2' => [
                '/some/path',
                false,
                1,
                -10,
                'text',
            ],
            'empty filepath' => [
                '',
                false,
                1,
                0,
                'text',
            ],
        ];
    }
}
