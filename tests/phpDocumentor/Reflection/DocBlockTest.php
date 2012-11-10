<?php
/**
 * phpDocumentor DocBlock Test
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection;

/**
 * Test class for phpDocumentor\Reflection\DocBlock
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description.
 *
 * This is a long description.
 *
 * @see \MyClass
 * @return void
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.',
            $object->getShortDescription()
        );
        $this->assertEquals(
            'This is a long description.',
            $object->getLongDescription()->getContents()
        );
        $this->assertEquals(2, count($object->getTags()));
        $this->assertTrue($object->hasTag('see'));
        $this->assertTrue($object->hasTag('return'));
        $this->assertFalse($object->hasTag('category'));
    }

    public function testConstructFromReflector()
    {
        $object = new DocBlock(new \ReflectionClass($this));
        $this->assertEquals(
            'Test class for phpDocumentor\Reflection\DocBlock',
            $object->getShortDescription()
        );
        $this->assertEquals('', $object->getLongDescription()->getContents());
        $this->assertEquals(4, count($object->getTags()));
        $this->assertTrue($object->hasTag('author'));
        $this->assertTrue($object->hasTag('copyright'));
        $this->assertTrue($object->hasTag('license'));
        $this->assertTrue($object->hasTag('link'));
        $this->assertFalse($object->hasTag('category'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnInvalidObject()
    {
        $object = new DocBlock($this);
    }

    public function testDotSeperation()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description. This is a long description.
 * This is a continuation of the long description.
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.',
            $object->getShortDescription()
        );
        $this->assertEquals(
            "This is a long description.\nThis is a continuation of the long "
            ."description.",
            $object->getLongDescription()->getContents()
        );
    }

    public function testTagCaseSensitivity()
    {
        $fixture = <<<DOCBLOCK
/**
 * This is a short description.
 *
 * This is a long description.
 *
 * @method null something()
 * @Method({"GET", "POST"})
 */
DOCBLOCK;
        $object = new DocBlock($fixture);
        $this->assertEquals(
            'This is a short description.',
            $object->getShortDescription()
        );
        $this->assertEquals(
            'This is a long description.',
            $object->getLongDescription()->getContents()
        );
        $tags = $object->getTags();
        $this->assertEquals(2, count($tags));
        $this->assertTrue($object->hasTag('method'));
        $this->assertTrue($object->hasTag('Method'));
        $this->assertInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag\MethodTag',
            $tags[0]
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag',
            $tags[1]
        );
        $this->assertNotInstanceOf(
            __NAMESPACE__ . '\DocBlock\Tag\MethodTag',
            $tags[1]
        );
    }

    /**
     * Tests whether a type is expanded with the given namespace and that a
     * keyword is not expanded.
     *
     * @covers \phpDocumentor\Reflection\DocBlock::expandType()
     *
     * @return void
     */
    public function testExpandTypeUsingNamespace()
    {
        $docblock = new DocBlock('', '\My\Namespace');
        $this->assertEquals('\My\Namespace\Mine', $docblock->expandType('Mine'));
    }

    /**
     * Tests whether a type is expanded when no namespace is given.
     *
     * @covers \phpDocumentor\Reflection\DocBlock::expandType()
     *
     * @return void
     */
    public function testExpandTypeWithoutNamespace()
    {
        $docblock = new DocBlock('');
        $this->assertEquals('\Mine', $docblock->expandType('Mine'));
    }

    /**
     * Tests whether a type is expanded with the given namespace when an alias
     * is provided.
     *
     * @covers \phpDocumentor\Reflection\DocBlock::expandType()
     *
     * @return void
     */
    public function testExpandTypeUsingNamespaceAlias()
    {
        $docblock = new DocBlock(
            '',
            '\My\Namespace',
            array('Alias' => '\My\Namespace\Alias')
        );

        // first try a normal resolution without alias
        $this->assertEquals(
            '\My\Namespace\Al',
            $docblock->expandType('Al')
        );

        // try to use the alias
        $this->assertEquals(
            '\My\Namespace\Alias\Al',
            $docblock->expandType('Alias\Al')
        );
    }

    /**
     * Tests whether the keywords that should not be converted are not converted.
     *
     * @param string $keyword The keyword that is to be tested; this is provided
     *     by the dataprovider.
     *
     * @covers \phpDocumentor\Reflection\DocBlock::expandType()
     *
     * @dataProvider getNonExpandableKeywordsForExpandType
     *
     * @return void
     */
    public function testThatExpandTypeDoesNotExpandAllKeywords($keyword)
    {
        $docblock = new DocBlock('', '\My\Namespace');
        $this->assertSame($keyword, $docblock->expandType($keyword));
    }

    public function getNonExpandableKeywordsForExpandType()
    {
        return array(
            array(null),
            array('string'), array('int'), array('integer'), array('bool'),
            array('boolean'), array('float'), array('double'), array('object'),
            array('mixed'), array('array'), array('resource'), array('void'),
            array('null'), array('callback'), array('false'), array('true'),
            array('self'), array('$this'), array('callable')
        );
    }
}
