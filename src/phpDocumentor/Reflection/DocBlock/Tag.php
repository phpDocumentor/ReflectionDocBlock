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

use phpDocumentor\Reflection\DocBlock;

/**
 * Parses a tag definition for a DocBlock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class Tag implements \Reflector
{
    /** @var string Name of the tag */
    protected $tag = '';

    /** @var string Content of the tag */
    protected $content = '';

    /** @var string Description of the content of this tag */
    protected $description = '';

    /** @var array The description, as an array of strings and Tag objects. */
    protected $parsedDescription = null;

    /** @var Location Location of the tag. */
    protected $location = null;

    /** @var DocBlock The DocBlock which this tag belongs to. */
    protected $docblock = null;
    
    /**
     * @var array An array with a tag as a key, and an FQCN to a class that
     *     handles it as an array value. The class is expected to inherit this
     *     class.
     */
    private static $tagHandlerMappings = array(
        'author'
            => '\phpDocumentor\Reflection\DocBlock\Tag\AuthorTag',
        'covers'
            => '\phpDocumentor\Reflection\DocBlock\Tag\CoversTag',
        'deprecated'
            => '\phpDocumentor\Reflection\DocBlock\Tag\DeprecatedTag',
        'example'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ExampleTag',
        'link'
            => '\phpDocumentor\Reflection\DocBlock\Tag\LinkTag',
        'method'
            => '\phpDocumentor\Reflection\DocBlock\Tag\MethodTag',
        'param'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ParamTag',
        'property-read'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyReadTag',
        'property'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyTag',
        'property-write'
            => '\phpDocumentor\Reflection\DocBlock\Tag\PropertyWriteTag',
        'return'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ReturnTag',
        'see'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SeeTag',
        'since'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SinceTag',
        'source'
            => '\phpDocumentor\Reflection\DocBlock\Tag\SourceTag',
        'throw'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag',
        'throws'
            => '\phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag',
        'uses'
            => '\phpDocumentor\Reflection\DocBlock\Tag\UsesTag',
        'var'
            => '\phpDocumentor\Reflection\DocBlock\Tag\VarTag',
        'version'
            => '\phpDocumentor\Reflection\DocBlock\Tag\VersionTag'
    );

    /**
     * Factory method responsible for instantiating the correct sub type.
     *
     * @param string   $tag_line The text for this tag, including description.
     * @param DocBlock $docblock The DocBlock which this tag belongs to.
     * @param Location $location Location of the tag.
     *
     * @throws \InvalidArgumentException if an invalid tag line was presented.
     *
     * @return static A new tag object.
     */
    final public static function createInstance(
        $tag_line,
        DocBlock $docblock = null,
        Location $location = null
    ) {
        if (!preg_match(
            '/^@([\w\-\_\\\\]+)(?:\s*([^\s].*)|$)?/us',
            $tag_line,
            $matches
        )) {
            throw new \InvalidArgumentException(
                'Invalid tag_line detected: ' . $tag_line
            );
        }

        if (isset(self::$tagHandlerMappings[$matches[1]])) {
            $handler = self::$tagHandlerMappings[$matches[1]];
            return new $handler(
                $matches[1],
                isset($matches[2]) ? $matches[2] : '',
                $docblock,
                $location
            );
        }
        return new self(
            $matches[1],
            isset($matches[2]) ? $matches[2] : '',
            $docblock,
            $location
        );
    }

    /**
     * Registers a handler for tags.
     * 
     * Registers a handler for tags. The class specified is autoloaded if it's
     * not available. It must inherit from this class.
     * 
     * @param string      $tag     Name of tag to regiser a handler for.
     * @param string|null $handler FQCN of handler. Specifing NULL removes the
     *     handler for the specified tag, if any.
     * 
     * @return bool TRUE on success, FALSE on failure.
     */
    final public static function registerTagHandler($tag, $handler)
    {
        $tag = trim((string)$tag);

        if (null === $handler) {
            unset(self::$tagHandlerMappings[$tag]);
            return true;
        }

        if ('' !== $tag
            && class_exists($handler, true)
            && is_subclass_of($handler, __CLASS__)
        ) {
            self::$tagHandlerMappings[$tag] = $handler;
            return true;
        }

        return false;
    }

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string   $type     Name of the tag.
     * @param string   $content  The contents of the given tag.
     * @param DocBlock $docblock The DocBlock which this tag belongs to.
     * @param Location $location Location of the tag.
     */
    public function __construct(
        $type,
        $content,
        DocBlock $docblock = null,
        Location $location = null
    ) {
        $this->tag         = $type;
        $this->content     = $content;
        $this->description = trim($content);
        $this->docblock    = $docblock;
        $this->location    = $location;
    }

    /**
     * Returns the name of this tag.
     *
     * @return string
     */
    public function getName()
    {
        return $this->tag;
    }

    /**
     * Returns the content of this tag.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the description component of this tag.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the parsed text of this description.
     * 
     * @return array An array of strings and tag objects, in the order they
     *     occur within the description.
     */
    public function getParsedDescription()
    {
        if (null === $this->parsedDescription) {
            $description = new Description($this->description, $this->docblock);
            $this->parsedDescription = $description->getParsedContents();
        }
        return $this->parsedDescription;
    }

    /**
     * Get the location of the tag.
     *
     * @return Location Tag's location.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection and implement it.
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
