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

namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\DocBlock\Context;

/**
 * @coversDefaultClass phpDocumentor\Reflection\Types\Resolver
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $keyword
     * @param string $expectedClass
     *
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses         phpDocumentor\Reflection\DocBlock\Context
     * @uses         phpDocumentor\Reflection\Types\Array_
     *
     * @dataProvider provideKeywords
     */
    public function testResolvingKeywords($keyword, $expectedClass)
    {
        $fixture = new Resolver();

        $resolvedType = $fixture->resolve($keyword, new Context(''));

        $this->assertInstanceOf($expectedClass, $resolvedType);
    }

    /**
     * @param string $fqsen
     *
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses         phpDocumentor\Reflection\DocBlock\Context
     * @uses         phpDocumentor\Reflection\Fqsen
     *
     * @dataProvider provideFqsen
     */
    public function testResolvingFQSENs($fqsen)
    {
        $fixture = new Resolver();

        $resolvedType = $fixture->resolve($fqsen, new Context(''));

        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType);
        $this->assertSame($fqsen, (string)$resolvedType);
    }


    /**
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses phpDocumentor\Reflection\DocBlock\Context
     * @uses phpDocumentor\Reflection\Fqsen
     */
    public function testResolvingRelativeQSENsBasedOnNamespace()
    {
        $fixture = new Resolver();

        $resolvedType = $fixture->resolve('DocBlock', new Context('phpDocumentor\Reflection'));

        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType);
        $this->assertSame('\phpDocumentor\Reflection\DocBlock', (string)$resolvedType);
    }

    /**
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses phpDocumentor\Reflection\DocBlock\Context
     * @uses phpDocumentor\Reflection\Fqsen
     */
    public function testResolvingRelativeQSENsBasedOnNamespaceAlias()
    {
        $fixture = new Resolver();

        $resolvedType = $fixture->resolve(
            'm\MockInterface',
            new Context('phpDocumentor\Reflection', ['m' => '\Mockery'])
        );

        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType);
        $this->assertSame('\Mockery\MockInterface', (string)$resolvedType);
    }

    /**
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses phpDocumentor\Reflection\DocBlock\Context
     * @uses phpDocumentor\Reflection\Types\Array_
     * @uses phpDocumentor\Reflection\Types\String
     */
    public function testResolvingTypedArrays()
    {
        $fixture = new Resolver();

        /** @var Array_ $resolvedType */
        $resolvedType = $fixture->resolve('string[]', new Context(''));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $resolvedType);
        $this->assertSame('string[]', (string)$resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Mixed', $resolvedType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String', $resolvedType->getValueType());
    }

    /**
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses phpDocumentor\Reflection\DocBlock\Context
     * @uses phpDocumentor\Reflection\Types\Array_
     * @uses phpDocumentor\Reflection\Types\String
     */
    public function testResolvingNestedTypedArrays()
    {
        $fixture = new Resolver();

        /** @var Array_ $resolvedType */
        $resolvedType = $fixture->resolve('string[][]', new Context(''));

        /** @var Array_ $childValueType */
        $childValueType = $resolvedType->getValueType();

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $resolvedType);

        $this->assertSame('string[][]', (string)$resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Mixed', $resolvedType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $childValueType);

        $this->assertSame('string[]', (string)$childValueType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Mixed', $childValueType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String', $childValueType->getValueType());
    }

    /**
     * @covers ::resolve
     * @covers ::<private>
     *
     * @uses phpDocumentor\Reflection\DocBlock\Context
     * @uses phpDocumentor\Reflection\Types\Compound
     * @uses phpDocumentor\Reflection\Types\String
     * @uses phpDocumentor\Reflection\Fqsen
     */
    public function testResolvingCompoundTypes()
    {
        $fixture = new Resolver();

        /** @var Compound $resolvedType */
        $resolvedType = $fixture->resolve('string|Reflection\DocBlock', new Context('phpDocumentor'));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType);
        $this->assertSame('string|\phpDocumentor\Reflection\DocBlock', (string)$resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String', $resolvedType->get(0));
        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType->get(1));
    }

    /**
     * @covers ::resolve
     * @uses phpDocumentor\Reflection\DocBlock\Context
     *
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfTypeIsEmpty()
    {
        $fixture = new Resolver();
        $fixture->resolve(' ', new Context(''));
    }

    /**
     * @covers ::resolve
     * @uses phpDocumentor\Reflection\DocBlock\Context
     *
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownIfTypeIsNotAString()
    {
        $fixture = new Resolver();
        $fixture->resolve(['a'], new Context(''));
    }

    public function provideKeywords()
    {
        return [
            ['string', 'phpDocumentor\Reflection\Types\String'],
            ['int', 'phpDocumentor\Reflection\Types\Integer'],
            ['integer', 'phpDocumentor\Reflection\Types\Integer'],
            ['float', 'phpDocumentor\Reflection\Types\Float'],
            ['double', 'phpDocumentor\Reflection\Types\Float'],
            ['bool', 'phpDocumentor\Reflection\Types\Boolean'],
            ['boolean', 'phpDocumentor\Reflection\Types\Boolean'],
            ['resource', 'phpDocumentor\Reflection\Types\Resource'],
            ['null', 'phpDocumentor\Reflection\Types\Null_'],
            ['callable', 'phpDocumentor\Reflection\Types\Callable_'],
            ['callback', 'phpDocumentor\Reflection\Types\Callable_'],
            ['array', 'phpDocumentor\Reflection\Types\Array_'],
            ['scalar', 'phpDocumentor\Reflection\Types\Scalar'],
            ['object', 'phpDocumentor\Reflection\Types\Object_'],
            ['mixed', 'phpDocumentor\Reflection\Types\Mixed'],
            ['void', 'phpDocumentor\Reflection\Types\Void'],
            ['$this', 'phpDocumentor\Reflection\Types\This'],
            ['static', 'phpDocumentor\Reflection\Types\Static_'],
            ['self', 'phpDocumentor\Reflection\Types\Self_'],
        ];
    }

    public function provideFqsen()
    {
        return [
            'namespace' => ['\phpDocumentor\Reflection'],
            'class' => ['\phpDocumentor\Reflection\DocBlock'],
            'function' => ['\DI\object()'],
            'constant' => ['\phpDocumentor\Reflection\GLOBAL_CONSTANT'],
            'classConstant' => ['\phpDocumentor\Reflection\DocBlock::CONSTANT'],
            'property' => ['\phpDocumentor\Reflection\DocBlock::$summary'],
            'method' => ['\phpDocumentor\Reflection\DocBlock::getSummary()'],
        ];
    }
}
