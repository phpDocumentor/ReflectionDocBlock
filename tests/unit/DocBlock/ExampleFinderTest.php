<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\ExampleFinder
 * @covers ::<private>
 */
class ExampleFinderTest extends TestCase
{
    /** @var ExampleFinder */
    private $fixture;

    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        $this->fixture = new ExampleFinder();
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Example
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::find
     * @covers ::getSourceDirectory
     */
    public function testFileNotFound(): void
    {
        $example = new Example('./example.php', false, 1, 0, 'Test');
        $this->assertSame('** File not found : ./example.php **', $this->fixture->find($example));
    }
}
