<?php
/**
 * phpDocumentor Param tag test.
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
 * Test class for \phpDocumentor\Reflection\DocBlock\ParamTag.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class ParamTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\ParamTag can
     * understand the @param DocBlock.
     *
     * @param string $type
     * @param string $content
     * @param string $extractedType
     * @param string $extractedVarName
     * @param string $extractedDescription
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ParamTag::__construct
     *
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $type,
        $content,
        $extractedType,
        $extractedVarName,
        $extractedDescription
    ) {
        $tag = new ParamTag($type, $content);

        $this->assertEquals($extractedType, $tag->getTypes());
        $this->assertEquals($extractedVarName, $tag->getVariableName());
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
            array('param', 'int', array('int'), '', ''),
            array('param', '$bob', array(), '$bob', ''),
            array(
                'param', 'int Number of bobs', array('int'), '',
                'Number of bobs'
            ),
            array('param', 'int $bob', array('int'), '$bob', ''),
            array(
                'param', 'int $bob Number of bobs', array('int'), '$bob',
                'Number of bobs'
            ),
        );
    }
}
