<?php
/**
 * phpDocumentor Covers Tag Test
 * 
 * PHP version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Type;

/**
 * Test class for phpDocumentor_Reflection_DocBlock
 * 
 * @covers phpDocumentor\Reflection\DocBlock\Type\Collection
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::__construct
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespace
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespaceAliases
     * 
     * @return void
     */
    public function testConstruct()
    {
        $collection = new Collection();
        $this->assertCount(0, $collection);
        $this->assertEquals('\\', $collection->getNamespace());
        $this->assertCount(0, $collection->getNamespaceAliases());
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::__construct
     * 
     * @return void
     */
    public function testConstructWithTypes()
    {
        $collection = new Collection(array('integer', 'string'));
        $this->assertCount(2, $collection);
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::__construct
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespace
     * 
     * @return void
     */
    public function testConstructWithNamespace()
    {
        $collection = new Collection(array(), '\My\Space');
        $this->assertEquals('\My\Space\\', $collection->getNamespace());

        $collection = new Collection(array(), 'My\Space');
        $this->assertEquals('\My\Space\\', $collection->getNamespace());

        $collection = new Collection(array(), null);
        $this->assertEquals('\\', $collection->getNamespace());
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::__construct
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespaceAliases
     * 
     * @return void
     */
    public function testConstructWithNamespaceAliases()
    {
        $fixture = array('a' => 'b');
        $collection = new Collection(array(), null, $fixture);
        $this->assertEquals($fixture, $collection->getNamespaceAliases());
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::setNamespace
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespace
     * 
     * @return void
     */
    public function testSetAndGetNamespace()
    {
        $collection = new Collection();
        $this->assertEquals('\\', $collection->getNamespace());

        $collection->setNamespace('My');
        $this->assertEquals('\My\\', $collection->getNamespace());

        $collection->setNamespace('\My');
        $this->assertEquals('\My\\', $collection->getNamespace());

        $collection->setNamespace('\My\\');
        $this->assertEquals('\My\\', $collection->getNamespace());
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::setNamespaceAliases
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::getNamespaceAliases
     * 
     * @return void
     */
    public function testSetAndGetNamespaceAliases()
    {
        $collection = new Collection();
        $this->assertEmpty($collection->getNamespaceAliases());

        $collection->setNamespaceAliases(array('My'));
        $this->assertEquals(array('My'), $collection->getNamespaceAliases());
    }

    /**
     * @param $fixture
     * @param $expected
     *
     * @dataProvider provideTypesToExpand
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::add
     * 
     * @return void
     */
    public function testAdd($fixture, $expected)
    {
        $collection = new Collection();
        $collection->setNamespace('\My\Space');
        $collection->setNamespaceAliases(array('Alias' => '\My\Space\Aliasing'));
        $collection->add($fixture);

        $this->assertSame($expected, $collection->getArrayCopy());
    }

    /**
     * @param $fixture
     * @param $expected
     *
     * @dataProvider provideTypesToExpandWithoutNamespace
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::add
     * 
     * @return void
     */
    public function testAddWithoutNamespace($fixture, $expected)
    {
        $collection = new Collection();
        $collection->setNamespaceAliases(array('Alias' => '\My\Space\Aliasing'));
        $collection->add($fixture);

        $this->assertSame($expected, $collection->getArrayCopy());
    }

    /**
     * @covers phpDocumentor\Reflection\DocBlock\Type\Collection::add
     * @expectedException InvalidArgumentException
     * 
     * @return void
     */
    public function testAddWithInvalidArgument()
    {
        $collection = new Collection();
        $collection->add(array());
    }

    /**
     * Returns the types and their expected values to test the retrieval of
     * types.
     *
     * @param string $method    Name of the method consuming this data provider.
     * @param string $namespace Name of the namespace to user as basis.
     *
     * @return string[]
     */
    public function provideTypesToExpand($method, $namespace = '\My\Space\\')
    {
        return array(
            array('', array()),
            array(' ', array()),
            array('int', array('int')),
            array('int ', array('int')),
            array('string', array('string')),
            array('DocBlock', array($namespace.'DocBlock')),
            array('DocBlock[]', array($namespace.'DocBlock[]')),
            array(' DocBlock ', array($namespace.'DocBlock')),
            array('\My\Space\DocBlock', array('\My\Space\DocBlock')),
            array('Alias\DocBlock', array('\My\Space\Aliasing\DocBlock')),
            array(
                'DocBlock|Tag',
                array($namespace .'DocBlock', $namespace .'Tag')
            ),
            array(
                'DocBlock|null',
                array($namespace.'DocBlock', 'null')
            ),
            array(
                '\My\Space\DocBlock|Tag',
                array('\My\Space\DocBlock', $namespace.'Tag')
            ),
            array(
                'DocBlock[]|null',
                array($namespace.'DocBlock[]', 'null')
            ),
            array(
                'DocBlock[]|int[]',
                array($namespace.'DocBlock[]', 'int[]')
            ),
        );
    }

    /**
     * Returns the types and their expected values to test the retrieval of
     * types when no namespace is available.
     *
     * @param string $method Name of the method consuming this data provider.
     *
     * @return string[]
     */
    public function provideTypesToExpandWithoutNamespace($method)
    {
        return $this->provideTypesToExpand($method, '\\');
    }

}

