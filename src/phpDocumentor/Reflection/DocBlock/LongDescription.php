<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
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
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class LongDescription implements \Reflector
{
    /** @var string */
    protected $contents = '';

    /** @var array The contents, as an array of strings and Tag objects. */
    protected $parsedContents = null;

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
     * Returns the parsed text of this description.
     *
     * @return array An array of strings and tag objects, in the order they
     *     occur within the description.
     */
    public function getParsedContents()
    {
        if (null === $this->parsedContents) {
            $this->parsedContents = preg_split(
                '/\{
                    # "{@}" is not a valid inline tag. This ensures that
                    # we do not treat it as one, but treat it literally.
                    (?!@\})
                    # We want to capture the whole tag line, but without the
                    # inline tag delimiters.
                    (\@
                        # Match everything up to the next delimiter.
                        [^{}]*
                        # Nested inline tag content should not be captured, or
                        # it will appear in the result separately.
                        (?:
                            # Match nested inline tags.
                            (?:
                                # Because we did not catch the tag delimiters
                                # earlier, we must be explicit with them here.
                                # Notice that this also matches "{}", as a way
                                # to later introduce it as an escape sequence.
                                \{(?1)?\}
                                |
                                # Make sure we match hanging "{".
                                \{
                            )
                            # Match content after the nested inline tag.
                            [^{}]*
                        )* # If there are more inline tags, match them as well.
                           # We use "*" since there may not be any nested inline
                           # tags.
                    )
                \}/Sux',
                $this->contents,
                null,
                PREG_SPLIT_DELIM_CAPTURE
            );
            for ($i=1, $l = count($this->parsedContents); $i<$l; $i += 2) {
                $this->parsedContents[$i] = Tag::createInstance(
                    $this->parsedContents[$i]
                );
            }

            //In order to allow "literal" inline tags, the otherwise invalid
            //sequence "{@}" is changed to "@", and "{}" is changed to "}".
            //See unit tests for examples.
            for ($i=0, $l = count($this->parsedContents); $i<$l; $i += 2) {
                $this->parsedContents[$i] = str_replace(
                    array('{@}', '{}'),
                    array('@', '}'),
                    $this->parsedContents[$i]
                );
            }
        }
        return $this->parsedContents;
    }

    /**
     * Return a formatted variant of the Long Description using MarkDown.
     *
     * @todo this should become a more intelligent piece of code where the
     *     configuration contains a setting what format long descriptions are.
     * 
     * @codeCoverageIgnore Will be removed soon, in favor of adapters at
     *     PhpDocumentor itself that will process text in various formats.
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

        if (class_exists('dflydev\markdown\MarkdownExtraParser')) {
            $markdown = new \dflydev\markdown\MarkdownExtraParser();
            $result = $markdown->transformMarkdown($result);
        }

        return trim($result);
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection
     *     and implement it.
     *
     * @return void
     * @codeCoverageIgnore Not yet implemented
     */
    public static function export()
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Returns the exported information (we should use the export static method
     * BUT this throws an exception at this point).
     *
     * @return string
     * @codeCoverageIgnore Not yet implemented
     */
    public function __toString()
    {
        return 'Not yet implemented';
    }
}
