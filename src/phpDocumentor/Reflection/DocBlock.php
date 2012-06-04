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

namespace phpDocumentor\Reflection;

/**
 * Parses the DocBlock for any structure.
 *
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class DocBlock implements \Reflector
{
    /** @var string The opening line for this docblock. */
    protected $short_description = '';

    /**
     * @var \phpDocumentor\Reflection\DocBlock\LongDescription The actual description
     *     for this docblock.
     */
    protected $long_description = null;

    /**
     * @var \phpDocumentor\Reflection\DocBlock\Tags[] An array containing all the tags
     *     in this docblock; except inline.
     */
    protected $tags = array();

    /**
     * Parses the given docblock and populates the member fields.
     *
     * @param string|\Reflector $docblock A docblock comment (including asterisks)
     *     or reflector supporting the getDocComment method.
     */
    public function __construct($docblock)
    {
        if (is_object($docblock)) {
            if (!method_exists($docblock, 'getDocComment')) {
                throw new \InvalidArgumentException(
                    'Invalid object passed; the given reflector must support '
                    . 'the getDocComment method'
                );
            }

            $docblock = $docblock->getDocComment();
        }

        $docblock = $this->cleanInput($docblock);

        list($short, $long, $tags) = $this->splitDocBlock($docblock);
        $this->short_description = $short;
        $this->long_description = new DocBlock\LongDescription($long);
        $this->parseTags($tags);
    }

    /**
     * Strips the asterisks from the DocBlock comment.
     *
     * @param string $comment String containing the comment text.
     *
     * @return string
     */
    protected function cleanInput($comment)
    {
        $comment = trim(
            preg_replace(
                '#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#', '$1', $comment
            )
        );

        // reg ex above is not able to remove */ from a single line docblock
        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }

        // normalize strings
        $comment = str_replace(array("\r\n", "\r"), "\n", $comment);

        return $comment;
    }

    /**
     * Splits the DocBlock into a short description, long description and
     * block of tags.
     *
     * @param string $comment Comment to split into the sub-parts.
     *
     * @author RichardJ Special thanks to RichardJ for the regex responsible
     *     for the split/
     *
     * @return string[] containing the short-, long description and an element
     *     containing the tags.
     */
    protected function splitDocBlock($comment)
    {
        if (strpos($comment, '@') === 0) {
            $matches = array('', '', $comment);
        } else {
            // clears all extra horizontal whitespace from the line endings
            // to prevent parsing issues
            $comment = preg_replace('~(?m)\h*$~', '', $comment);

            /*
             * Splits the docblock into a short description, long description and
             * tags section
             * - The short description is started from the first character until
             *   a dot is encountered followed by a whitespace OR
             *   two consecutive newlines (horizontal whitespace is taken into
             *   account to consider spacing errors)
             * - The long description, any character until a new line is
             *   encountered followed by an @ and word characters (a tag).
             *   This is optional.
             * - Tags; the remaining characters
             *
             * Big thanks to RichardJ for contributing this Regular Expression
             */
            preg_match(
                '/(?x)
        \A (
          [^\n.]+
          (?:
            (?! \. \s | \n{2} ) # disallow the first seperator here
            [\n.] (?! [ \t]* @\pL ) # disallow second seperator
            [^\n.]+
          )*
          \.?
        )
        (?:
          \s* # first seperator (actually newlines but it\'s all whitespace)
          (?! @\pL ) # disallow the rest, to make sure this one doesn\'t match,
          #if it doesn\'t exist
          (
            [^\n]+
            (?: \n+
              (?! [ \t]* @\pL ) # disallow second seperator (@param)
              [^\n]+
            )*
          )
        )?
        (\s+ [\s\S]*)? # everything that follows
        /', $comment, $matches
            );
            array_shift($matches);
        }

        while (count($matches) < 3) {
            $matches[] = '';
        }
        return $matches;
    }

    /**
     * Creates the tag objects.
     *
     * @param string $tags Tag block to parse.
     *
     * @return void
     */
    protected function parseTags($tags)
    {
        $result = array();
        foreach (explode("\n", trim($tags)) as $tag_line) {
            if (trim($tag_line) === '') {
                continue;
            }

            $tag_line = ltrim($tag_line);

            if (isset($tag_line[0]) && ($tag_line[0] === '@')) {
                $result[] = $tag_line;
            } else {
                if (count($result) == 0) {
                    throw new \LogicException(
                        'A tag block started with text instead of an actual tag,'
                        . ' this makes the tag block invalid: ' . $tags
                    );
                }

                $result[count($result) - 1] .= PHP_EOL . $tag_line;
            }
        }

        // create proper Tag objects
        foreach ($result as $key => $tag_line) {
            $result[$key] = DocBlock\Tag::createInstance($tag_line);
        }

        $this->tags = $result;
    }

    /**
     * Returns the opening line or also known as short description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * Returns the full description or also known as long description.
     *
     * @return \phpDocumentor\Reflection\DocBlock\LongDescription
     */
    public function getLongDescription()
    {
        return $this->long_description;
    }

    /**
     * Returns the tags for this DocBlock.
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns an array of tags matching the given name; if no tags are found
     * an empty array is returned.
     *
     * @param string $name String to search by.
     *
     * @return \phpDocumentor\Reflection\DocBlock_Tag[]
     */
    public function getTagsByName($name)
    {
        $result = array();

        /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() != $name) {
                continue;
            }

            $result[] = $tag;
        }

        return $result;
    }

    /**
     * Checks if a tag of a certain type is present in this DocBlock.
     *
     * @param string $name Tag name to check for.
     *
     * @return bool
     */
    public function hasTag($name)
    {
        /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection and
     *     implement it.
     *
     * @return string
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
