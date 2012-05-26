<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

/**
 * Parses a Long Description of a DocBlock.
 *
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class LongDescription implements \Reflector
{
    /** @var string */
    protected $contents = '';

    /** @var \phpDocumentor\Reflection\DocBlock\Tags[] */
    protected $tags = array();

    /**
     * Parses the string for inline tags and if the Markdown class is included;
     * format the found text.
     *
     * @param string $content the DocBlock contents without asterisks.
     */
    public function __construct($content)
    {
        if (preg_match('/\{\@(.+?)\}/', $content, $matches)) {
            array_shift($matches);
            foreach ($matches as $tag) {
                $this->tags[] = Tag::createInstance('@' . $tag);
            }
        }

        $this->contents = trim($content);
    }

    /**
     * Returns the text of this description.
     *
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Return a formatted variant of the Long Description using MarkDown.
     *
     * @todo this should become a more intelligent piece of code where the
     *     configuration contains a setting what format long descriptions are.
     *
     * @return string
     */
    public function getFormattedContents()
    {
        $result = $this->contents;

        // if the long description contains a plain HTML <code> element, surround
        // it with a pre element. Please note that we explicitly used str_replace
        // and not preg_replace to gain performance
        if (strpos($result, '<code>') !== false) {
            $result = str_replace(
                array('<code>', "<code>\r\n", "<code>\n", "<code>\r", '</code>'),
                array('<pre><code>', '<code>', '<code>', '<code>', '</code></pre>'),
                $result
            );
        }

        if (class_exists('\dflydev\markdown\MarkdownExtraParser')) {
            $md = new \dflydev\markdown\MarkdownExtraParser();
            $result = $md->transformMarkdown($result);
        }

        return trim($result);
    }

    /**
     * Returns a list of tags mentioned in the text.
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tags[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection and implement it.
     *
     * @return void
     */
    static public function export()
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Returns the exported information (we should use the export static method
     * BUT this throws an exception at this point).
     *
     * @return string
     */
    public function __toString()
    {
        return 'Not yet implemented';
    }
}