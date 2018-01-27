<?php
/**
 * phpDocumentor Return tag test.
 * 
 * PHP version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

use phpDocumentor\Reflection\DocBlock;

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\ReturnTag
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class ReturnTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag can
     * understand the {@}return DocBlock.
     *
     * @param string $type
     * @param string $content
     * @param string $extractedType
     * @param string $extractedTypes
     * @param string $extractedDescription
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $type,
        $content,
        $extractedType,
        $extractedTypes,
        $extractedDescription
    ) {
        $tag = new ReturnTag($type, $content);

        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($extractedType, $tag->getType());
        $this->assertEquals($extractedTypes, $tag->getTypes());
        $this->assertEquals($extractedDescription, $tag->getDescription());
    }

    /**
     * Data provider for testConstructorParsesInputsIntoCorrectFields()
     *
     * @return array
     */
    public function provideDataForConstructor()
    {
        return array(
            array('return', '', '', array(), ''),
            array('return', 'int', 'int', array('int'), ''),
            array(
                'return',
                'int Number of Bobs',
                'int',
                array('int'),
                'Number of Bobs'
            ),
            array(
                'return',
                'int|double Number of Bobs',
                'int|double',
                array('int', 'double'),
                'Number of Bobs'
            ),
            array(
                'return',
                "int Number of \n Bobs",
                'int',
                array('int'),
                "Number of \n Bobs"
            ),
            array(
                'return',
                " int Number of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            ),
            array(
                'return',
                "int\nNumber of Bobs",
                'int',
                array('int'),
                "Number of Bobs"
            )
        );
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::setType
     */
    public function testSetType()
    {
        $tag = new ReturnTag('throws', 'bool');
        $this->assertSame('@throws bool', (string)$tag);

        $tag->setType('int');
        $this->assertSame('@throws int ', (string)$tag);
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::addType
     */
    public function testAddType()
    {
        $tag = new ReturnTag('param', 'bool');
        $this->assertSame('@param bool', (string)$tag);

        $tag->addType('int');
        $this->assertSame('@param bool|int ', (string)$tag);
    }

    /**
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::getType
     */
    public function testGetType()
    {
        $tag = new ReturnTag(
            'return',
            $type = 'bool|Bar|\Closure|int[]|BarNS\Bar[]|\BarNS\Foo|$this|BarNS::foo',
            new DocBlock('/** @throws BarNS|Foo[] */', new DocBlock\Context('MyNS', array('BarNS' => '\Bar\Foo')))
        );
        $this->assertSame($type, $tag->getType(false));

        $expected = 'bool|\MyNS\Bar|\Closure|int[]|\Bar\Foo\Bar[]|\BarNS\Foo|$this|\Bar\Foo::foo';
        $this->assertSame($expected, $type = $tag->getType());

        $tag->setType($type);
        $this->assertSame("@return $expected ", (string)$tag);

        foreach ($tag->getDocBlock()->getTags() as $t) {
            $tag = $t;
            break;
        }
        $this->assertTrue($tag instanceof ThrowsTag);
        $this->assertSame('@throws BarNS|Foo[]', (string)$tag);

        /* @var ReturnTag $tag */
        $tag->setType($tag->getType());
        $this->assertSame('@throws \Bar\Foo|\MyNS\Foo[] ', (string)$tag);
    }
}
