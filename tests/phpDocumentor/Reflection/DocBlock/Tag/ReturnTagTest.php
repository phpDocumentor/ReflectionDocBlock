<?php
/**
 * phpDocumentor Return tag test.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

require_once __DIR__
    . '/../../../../../src/phpDocumentor/Reflection/DocBlock/Tag/ReturnTag.php';

/**
 * Test class for phpDocumentor_Reflection_DocBlock_ReturnTag.
 *
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class ReturnTagTest extends ParamTagTest
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag can
     * understand the Return DocBlock.
     *
     * @param string $content
     * @param string $extracted_type
     * @param string $extracted_description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::__construct
     *
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $content, $extracted_type, $extracted_description
    ) {
        $tag = new ReturnTag('return', $content);

        $this->assertEquals($extracted_type,          $tag->getTypes());
        $this->assertEquals($extracted_description,   $tag->getDescription());
    }

    /**
     * Tests whether the getTypes method correctly converts the given tags.
     *
     * @param string   $type     Type string to test
     * @param string[] $expected Array of expected types
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::getTypes()
     *
     * @dataProvider provideTypesToExpand
     *
     * @return void
     */
    public function testExpandTypeIntoCorrectFcqn($type, $expected)
    {
        $docblock = new \phpDocumentor\Reflection\DocBlock(
            '', '\My\Namespace', array('Alias' => '\My\Namespace\Aliasing')
        );

        $tag = new ReturnTag('return', $type);
        $tag->setDocBlock($docblock);
        $this->assertEquals($expected, $tag->getTypes());
    }

    /**
     * Data provider for testConstructorParsesInputsIntoCorrectFields()
     *
     * @return array
     */
    public function provideDataForConstructor()
    {
        return array(
            array('', array(''), ''),
            array('int', array('int'), ''),
            array('int Number of Bobs', array('int'), 'Number of Bobs'),
        );
    }
}
