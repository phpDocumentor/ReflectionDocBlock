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

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\ReturnTag.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class ReturnTagTest extends ParamTagTest
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag can
     * understand the Return DocBlock.
     *
     * @param string $content
     * @param string $extractedType
     * @param string $extractedDescription
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ReturnTag::__construct
     *
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $content,
        $extractedType,
        $extractedDescription
    ) {
        $tag = new ReturnTag('return', $content);

        $this->assertEquals($extractedType, $tag->getTypes());
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
            array('', array(), ''),
            array('int', array('int'), ''),
            array('int Number of Bobs', array('int'), 'Number of Bobs'),
        );
    }
}
