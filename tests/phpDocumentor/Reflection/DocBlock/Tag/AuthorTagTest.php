<?php
/**
 * phpDocumentor Author tag test.
 * 
 * PHP version 5.3
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\AuthorTag.
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class AuthorTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the \phpDocumentor\Reflection\DocBlock\Tag\AuthorTag can
     * understand the @author DocBlock.
     *
     * @param string $type
     * @param string $content
     * @param string $extractedType
     * @param string $extractedVarName
     * @param string $extractedDescription
     *
     * @dataProvider provideDataForConstructor
     *
     * @return void
     */
    public function testConstructorParsesInputsIntoCorrectFields(
        $type,
        $content,
        $extractedName,
        $extractedURIs,
        $extractedRole,
        $extractedDescription
    ) {
        $tag = new AuthorTag($type, $content);

        $this->assertEquals($extractedName, $tag->getAuthorName());
        $this->assertEquals($extractedURIs, $tag->getAuthorURIs());
        $this->assertEquals($extractedRole, $tag->getAuthorRole());
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
            array('author', 'Mike van Riel', 'Mike van Riel', array(), '', ''),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com>',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                '',
                ''
            ),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com >',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                '',
                ''
            ),
            array(
                'author',
                'Mike van Riel < mike.vanriel@naenius.com>',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                '',
                ''
            ),
            array(
                'author',
                'Mike van Riel < mike.vanriel@naenius.com >',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                '',
                ''
            ),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com @mvriel>',
                'Mike van Riel',
                array('mike.vanriel@naenius.com', '@mvriel'),
                '',
                ''
            ),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com> (lead)',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                'lead',
                ''
            ),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com> (lead) The one',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                'lead',
                'The one'
            ),
            array(
                'author',
                'Mike van Riel <mike.vanriel@naenius.com> The one',
                'Mike van Riel',
                array('mike.vanriel@naenius.com'),
                '',
                'The one'
            ),
            array(
                'author',
                'Mike van Riel <> The one',
                'Mike van Riel',
                array(),
                '',
                'The one'
            )
        );
    }
}
