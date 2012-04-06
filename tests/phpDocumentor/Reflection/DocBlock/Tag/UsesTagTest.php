<?php
/**
 * phpDocumentor Uses Tag Test
 *
 * @author     Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

require_once __DIR__ . '/../../../../../src/phpDocumentor/Reflection/DocBlock/Tag/UsesTag.php';

/**
 * Test class for phpDocumentor_Reflection_DocBlock_Tag_Uses
 *
 * @author     Daniel O'Connor <daniel.oconnor@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class UsesTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\UsesTag can create
     * a link for the @uses doc block
     *
     * @param string $type
     * @param string $content
     * @param string $exName
     * @param string $exContent
     * @param string $exReference
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\UsesTag::__construct
     * @dataProvider provideDataForConstuctor
     *
     * @return void
     */
    public function testConstructorParesInputsIntoCorrectFields(
        $type, $content, $exName, $exContent, $exDescription, $exReference
    )
    {
        $tag = new UsesTag($type, $content);

        $actualName        = $tag->getName();
        $actualContent     = $tag->getContent();
        $actualDescription = $tag->getDescription();
        $actualReference   = $tag->getReference();

        $this->assertEquals($exName, $actualName);
        $this->assertEquals($exContent, $actualContent);
        $this->assertEquals($exDescription, $actualDescription);
        $this->assertEquals($exReference, $actualReference);
    }

    /**
     * Data provider for testConstructorParesInputsIntoCorrectFields
     *
     * @return array
     */
    public function provideDataForConstuctor()
    {
        // $type, $content, $exName, $exContent, $exDescription, $exReference
        return array(
            array(
                'uses',
                'Foo::bar()',
                'uses',
                'Foo::bar()',
                '',
                'Foo::bar()'
            ),
            array(
                'uses',
                'Foo::bar() Testing',
                'uses',
                'Foo::bar() Testing',
                'Testing',
                'Foo::bar()',
            ),
            array(
                'uses',
                'Foo::bar() Testing comments',
                'uses',
                'Foo::bar() Testing comments',
                'Testing comments',
                'Foo::bar()',
            ),
        );
    }
}
