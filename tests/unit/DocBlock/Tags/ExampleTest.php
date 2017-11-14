<?php

namespace DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Example
 */
class ExampleTest extends TestCase
{

    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getContent
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testExampleWithoutContent()
    {
        $tag = Example::create('"example1.php"');
        $this->assertEquals('"example1.php"', $tag->getContent());
        $this->assertEquals('', $tag->getDescription());
        $this->assertEquals('example', $tag->getName());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getDescription
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testWithDescription()
    {
        $tag = Example::create('"example1.php" some text');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals('some text', $tag->getDescription());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testStartlineIsParsed()
    {
        $tag = Example::create('"example1.php" 10');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getDescription
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testAllowOmitingLineCount()
    {
        $tag = Example::create('"example1.php" 10 some text');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
        $this->assertEquals('some text', $tag->getDescription());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getLineCount
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testLengthIsParsed()
    {
        $tag = Example::create('"example1.php" 10 5');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
        $this->assertEquals(5, $tag->getLineCount());
    }

    /**
     * @covers ::create
     * @covers ::__construct
     * @covers ::getFilePath
     * @covers ::getStartingLine
     * @covers ::getLineCount
     * @covers ::getDescription
     * @uses phpDocumentor\Reflection\DocBlock\Tags\BaseTag
     */
    public function testFullExample()
    {
        $tag = Example::create('"example1.php" 10 5 test text');
        $this->assertEquals('example1.php', $tag->getFilePath());
        $this->assertEquals(10, $tag->getStartingLine());
        $this->assertEquals(5, $tag->getLineCount());
        $this->assertEquals('test text', $tag->getDescription());
    }
}
