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

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Param
 * @covers ::<private>
 */
class ParamTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Param('myParameter', null, false, new Description('Description'));

        $this->assertSame('param', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param::isVariadic
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Param('myParameter', new String_(), true, new Description('Description'));
        $this->assertSame('@param string ...$myParameter Description', $fixture->render());

        $fixture = new Param('myParameter', new String_(), false, new Description('Description'));
        $this->assertSame('@param string $myParameter Description', $fixture->render());

        $fixture = new Param('myParameter', null, false, new Description('Description'));
        $this->assertSame('@param $myParameter Description', $fixture->render());

        $fixture = new Param('myParameter');
        $this->assertSame('@param $myParameter', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Param::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Param('myParameter');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getVariableName
     */
    public function testHasVariableName()
    {
        $expected = 'myParameter';

        $fixture = new Param($expected);

        $this->assertSame($expected, $fixture->getVariableName());
    }

    /**
     * @covers ::__construct
     * @covers ::getType
     */
    public function testHasType()
    {
        $expected = new String_();

        $fixture = new Param('myParameter', $expected);

        $this->assertSame($expected, $fixture->getType());
    }

    /**
     * @covers ::__construct
     * @covers ::isVariadic
     */
    public function testIfParameterIsVariadic()
    {
        $fixture = new Param('myParameter', new String_(), false);
        $this->assertFalse($fixture->isVariadic());

        $fixture = new Param('myParameter', new String_(), true);
        $this->assertTrue($fixture->isVariadic());
    }

    /**
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Param('1.0', null, false, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::isVariadic
     * @covers ::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\Types\String_
     */
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Param('myParameter', new String_(), true, new Description('Description'));

        $this->assertSame('string ...$myParameter Description', (string)$fixture);
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Param::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     */
    public function testFactoryMethod()
    {
        $typeResolver = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Param::create('string ...$myParameter My Description', $typeResolver, $descriptionFactory, $context);

        $this->assertSame('string ...$myParameter My Description', (string)$fixture);
        $this->assertSame('myParameter', $fixture->getVariableName());
        $this->assertInstanceOf(String_::class, $fixture->getType());
        $this->assertTrue($fixture->isVariadic());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Param::<public>
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Param::create('', new TypeResolver(), $descriptionFactory);
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Param::create([]);
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Param::create('body');
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Param::create('body', new TypeResolver());
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfVariableNameIsNotString()
    {
        new Param([]);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfVariadicIsNotBoolean()
    {
        new Param('', null, []);
    }
}
