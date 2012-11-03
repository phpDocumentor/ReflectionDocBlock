<?php
/**
 * phpDocumentor Link Tag Test
 *
 * @author     Ben Selby <benmatselby@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\Tag\LinkTag
 *
 * @author     Ben Selby <benmatselby@gmail.com>
 * @copyright  Copyright (c) 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 */
class LinkTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\LinkTag can create
     * a link for the @link doc block
     *
     * @param string $type
     * @param string $content
     * @param string $exName
     * @param string $exContent
     * @param string $exDescription
     * @param string $exLink
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\LinkTag::__construct
     * @dataProvider provideDataForConstuctor
     *
     * @return void
     */
    public function testConstructorParesInputsIntoCorrectFields(
        $type, $content, $exName, $exContent, $exDescription, $exLink
    )
    {
        $tag = new LinkTag($type, $content);

        $actualName        = $tag->getName();
        $actualContent     = $tag->getContent();
        $actualDescription = $tag->getDescription();
        $actualLink        = $tag->getLink();

        $this->assertEquals($exName, $actualName);
        $this->assertEquals($exContent, $actualContent);
        $this->assertEquals($exDescription, $actualDescription);
        $this->assertEquals($exLink, $actualLink);
    }

    /**
     * Data provider for testConstructorParesInputsIntoCorrectFields
     *
     * @return array
     */
    public function provideDataForConstuctor()
    {
        // $type, $content, $exName, $exContent, $exDescription, $exLink
        return array(
            array(
                'link',
                'http://www.phpdoc.org/',
                'link',
                'http://www.phpdoc.org/',
                'http://www.phpdoc.org/',
                'http://www.phpdoc.org/'
            ),
            array(
                'link',
                'http://www.phpdoc.org/ Testing',
                'link',
                'http://www.phpdoc.org/ Testing',
                'Testing',
                'http://www.phpdoc.org/'
            ),
            array(
                'link',
                'http://www.phpdoc.org/ Testing comments',
                'link',
                'http://www.phpdoc.org/ Testing comments',
                'Testing comments',
                'http://www.phpdoc.org/'
            ),
        );
    }
}