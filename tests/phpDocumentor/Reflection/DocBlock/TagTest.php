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

    public function testTagHandlerUnregistration()
    {
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreUnreg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreUnreg
        );

        Tag::registerTagHandler('var', null);

        $tagPostUnreg = Tag::createInstance('@var mixed');
        $this->assertNotInstanceOf(
            $currentHandler,
            $tagPostUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostUnreg
        );

        Tag::registerTagHandler('var', $currentHandler);
    }

    public function testTagHandlerRegistration()
    {
        if (0 == ini_get('allow_url_include')) {
            $this->markTestSkipped('"data" URIs for includes are required.');
        }
        $currentHandler = __NAMESPACE__ . '\Tag\VarTag';
        $tagPreUnreg = Tag::createInstance('@var mixed');
        $this->assertInstanceOf(
            $currentHandler,
            $tagPreUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPreUnreg
        );

        require 'data:text/plain;base64,'. base64_encode(<<<TAG_HANDLER
<?php
    class MyVarHandler extends \phpDocumentor\Reflection\DocBlock\Tag {}
TAG_HANDLER
        );

        
        $this->assertFalse(Tag::registerTagHandler('var', 'Non existent'));
        $this->assertTrue(Tag::registerTagHandler('var', '\MyVarHandler'));
        $this->assertFalse(
            Tag::registerTagHandler('var', __NAMESPACE__ . '\TagTest')
        );

        $tagPostUnreg = Tag::createInstance('@var mixed');
        $this->assertNotInstanceOf(
            $currentHandler,
            $tagPostUnreg
        );
        $this->assertInstanceOf(
            __NAMESPACE__ . '\Tag',
            $tagPostUnreg
        );
        $this->assertInstanceOf(
            '\MyVarHandler',
            $tagPostUnreg
        );

        $this->assertTrue(Tag::registerTagHandler('var', $currentHandler));
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
