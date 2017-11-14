<?php

namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass phpDocumentor\Reflection\DocBlock\ExampleFinder
 * @covers ::<private>
 */
class ExampleFinderTest extends TestCase
{
    /** @var ExampleFinder */
    private $fixture;

    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        $this->fixture = new ExampleFinder();
    }

    /**
     * @covers ::find
     * @covers ::getSourceDirectory
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Example
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testFileNotFound()
    {
        $example = new Example('./example.php', false, 1, 0, new Description('Test'));
        $this->assertSame('** File not found : ./example.php **', $this->fixture->find($example));
    }
}
