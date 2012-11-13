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

    /** @var int Line number of the tag */
    protected $line_number = 0;

    /** @var \phpDocumentor\Reflection\DocBlock docblock class */
    protected $docblock;
    
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
     * @param string $tag_line The text for this tag, including description.
     *
     * @throws \InvalidArgumentException if an invalid tag line was presented.
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tag
     */
    final public static function createInstance($tag_line)
    {
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
                isset($matches[2]) ? $matches[2] : ''
            );
        }
        return new self($matches[1], isset($matches[2]) ? $matches[2] : '');
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
     * @param string $type    Name of the tag.
     * @param string $content The contents of the given tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;
        $this->description = $content;
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
            $description = new LongDescription($this->description);
            $this->parsedDescription = $description->getParsedContents();
        }
        return $this->parsedDescription;
    }

    /**
     * Set the tag line number
     *
     * @param int $number the line number of the tag
     *
     * @return void
     */
    public function setLineNumber($number)
    {
        $this->line_number = (int)$number;
    }

    /**
     * Get the line number of the tag
     *
     * @return int tag line number
     */
    public function getLineNumber()
    {
        return $this->line_number;
    }

    /**
     * Inject the docblock class
     *
     * This exposes some common functionality contained in the docblock abstract.
     *
     * @param object $docblock Object containing the DocBlock.
     *
     * @return void
     */
    public function setDocBlock($docblock)
    {
        $this->docblock = $docblock;
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
