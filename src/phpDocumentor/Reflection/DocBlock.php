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

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Context;
use phpDocumentor\Reflection\DocBlock\Location;

/**
 * Parses the DocBlock for any structure.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class DocBlock implements \Reflector
{
    /** @var string The opening line for this docblock. */
    protected $short_description = '';

    /**
     * @var DocBlock\Description The actual
     *     description for this docblock.
     */
    protected $long_description = null;

    /**
     * @var Tag[] An array containing all
     *     the tags in this docblock; except inline.
     */
    protected $tags = array();

    /** @var Context Information about the context of this DocBlock. */
    protected $context = null;

    /** @var Location Information about the location of this DocBlock. */
    protected $location = null;

    /**
     * Parses the given docblock and populates the member fields.
     *
     * The constructor may also receive namespace information such as the
     * current namespace and aliases. This information is used by some tags
     * (e.g. @return, @param, etc.) to turn a relative Type into a FQCN.
     *
     * @param \Reflector|string $docblock A docblock comment (including
     *     asterisks) or reflector supporting the getDocComment method.
     * @param Context           $context  The context in which the DocBlock
     *     occurs.
     * @param Location          $location The location within the file that this
     *     DocBlock occurs in.
     *
     * @throws \InvalidArgumentException if the given argument does not have the
     *     getDocComment method.
     */
    public function __construct(
        $docblock,
        Context $context = null,
        Location $location = null
    ) {
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
        $this->long_description = new DocBlock\Description($long, $this);
        $this->parseTags($tags);

        $this->context  = $context;
        $this->location = $location;
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
                '#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u',
                '$1',
                $comment
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
     *     for the split.
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
            $comment = preg_replace('/\h*$/Sum', '', $comment);

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
                '/
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
        /ux',
                $comment,
                $matches
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
        $tags = trim($tags);
        if ('' !== $tags) {
            if ('@' !== $tags[0]) {
                throw new \LogicException(
                    'A tag block started with text instead of an actual tag,'
                    . ' this makes the tag block invalid: ' . $tags
                );
            }
            foreach (explode("\n", $tags) as $tag_line) {
                if (trim($tag_line) === '') {
                    continue;
                }

                if (isset($tag_line[0]) && ($tag_line[0] === '@')) {
                    $result[] = $tag_line;
                } else {
                    $result[count($result) - 1] .= PHP_EOL . $tag_line;
                }
            }

            // create proper Tag objects
            foreach ($result as $key => $tag_line) {
                $result[$key] = Tag::createInstance($tag_line, $this);
            }
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
     * @return DocBlock\Description
     */
    public function getLongDescription()
    {
        return $this->long_description;
    }

    /**
     * Returns the current context.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns the current location.
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Returns the tags for this DocBlock.
     *
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns an array of tags matching the given name. If no tags are found
     * an empty array is returned.
     *
     * @param string $name String to search by.
     *
     * @return Tag[]
     */
    public function getTagsByName($name)
    {
        $result = array();

        /** @var Tag $tag */
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
        /** @var Tag $tag */
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Appends a tag at the end of the list of tags.
     * 
     * @param Tag $tag The tag to add.
     * 
     * @return Tag The newly added tag.
     * 
     * @throws \LogicException When the tag belongs to a different DocBlock.
     */
    public function appendTag(Tag $tag)
    {
        if (null === $tag->getDocBlock()) {
            $tag->setDocBlock($this);
        }
        
        if ($tag->getDocBlock() === $this) {
            $this->tags[] = $tag;
        } else {
            throw new \LogicException(
                'This tag belongs to a different DocBlock object.'
            );
        }

        return $tag;
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection and
     *     implement it.
     *
     * @return string
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
