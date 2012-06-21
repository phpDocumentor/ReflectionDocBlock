<?php
/**
 * phpDocumentor Param tag test.
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

require_once __DIR__ . '/../../../../../src/phpDocumentor/Reflection/DocBlock/Tag/ParamTag.php';

/**
 * Test class for phpDocumentor_Reflection_DocBlock_Param.
 *
 * @author     Mike van Riel <mike.vanriel@naenius.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class ParamTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\ParamTag can
     * understand the param DocBlock.
     *
     * @param string $type
     * @param string $content
     * @param string $extracted_type
     * @param string $extracted_variable_name
     * @param string $extracted_description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ParamTag::__construct
     *
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $type, $content, $extracted_type, $extracted_variable_name,
        $extracted_description
    ) {
        $tag = new ParamTag($type, $content);

        $this->assertEquals($extracted_type,          $tag->getTypes());
        $this->assertEquals($extracted_variable_name, $tag->getVariableName());
        $this->assertEquals($extracted_description,   $tag->getDescription());
    }

    /**
     * Tests whether the getTypes method correctly converts the given tags.
     *
     * @param string   $type     Type string to test
     * @param string[] $expected Array of expected types
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\ParamTag::getTypes()
     *
     * @dataProvider provideTypesToExpand
     */
    public function testExpandTypeIntoCorrectFcqn($type, $expected)
    {
        $docblock = new \phpDocumentor\Reflection\DocBlock(
            '', '\My\Namespace', array('Alias' => '\My\Namespace\Aliasing')
        );

        $tag = new ParamTag('param', $type.' $my_type');
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
            array('param', 'int', array(''), '', 'int'),
            array('param', '$bob', array(''), '$bob', ''),
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

    /**
     * Returns the types and their expected values to test the retrieval of
     * types.
     *
     * @return string[]
     */
    public function provideTypesToExpand()
    {
        return array(
            array('', array('')),
            array(' ', array('')),
            array('int', array('int')),
            array('int ', array('int')),
            array('string', array('string')),
            array('DocBlock', array('\My\Namespace\DocBlock')),
//            array(' DocBlock ', array('\My\Namespace\DocBlock')), FIXME
            array('Alias\DocBlock', array('\My\Namespace\Aliasing\DocBlock')),
            array(
                'DocBlock|Tag',
                array('\My\Namespace\DocBlock', '\My\Namespace\Tag')
            ),
            array(
                'DocBlock|null',
                array('\My\Namespace\DocBlock', 'null')
            ),
        );
    }
}
