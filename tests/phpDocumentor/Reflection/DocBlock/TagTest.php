<?php
/**
 * phpDocumentor Var Tag Test
 * 
 * PHP version 5.3
 *
 * @author    Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\Tag\VarTag
 *
 * @author    Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class TagTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @expectedException \InvalidArgumentException
     * 
     * @return void
     */
    public function testInvalidTagLine()
    {
        Tag::createInstance('Invalid tag line');
    }
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\VarTag can
     * understand the @var doc block.
     *
     * @param string $type
     * @param string $content
     * @param string $exDescription
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tag::getDescription
     * @covers \phpDocumentor\Reflection\DocBlock\Tag::getContent
     * @dataProvider provideDataForConstuctor
     *
     * @return void
     */
    public function testConstructorParesInputsIntoCorrectFields(
        $type,
        $content,
        $exDescription
    ) {
        $tag = new Tag($type, $content);

        $this->assertEquals($type, $tag->getName());
        $this->assertEquals($content, $tag->getContent());
        $this->assertEquals($exDescription, $tag->getDescription());
    }

    /**
     * Data provider for testConstructorParesInputsIntoCorrectFields
     *
     * @return array
     */
    public function provideDataForConstuctor()
    {
        // $type, $content, $exDescription
        return array(
            array(
                'unknown',
                'some content',
                'some content',
            ),
            array(
                'unknown',
                '',
                '',
            ),
            array(
                '',
                'unknown',
                'unknown',
            )
        );
    }
}
