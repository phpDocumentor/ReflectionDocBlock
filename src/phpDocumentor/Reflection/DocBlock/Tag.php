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
 * Parses a tag definition for a DocBlock.
 *
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class Tag implements \Reflector
{
    /** @var string Name of the tag */
    protected $tag = '';

    /** @var string content of the tag */
    protected $content = '';

    /** @var string description of the content of this tag */
    protected $description = '';

    /** @var int line number of the tag */
    protected $line_number = 0;

    /** @var \phpDocumentor\Reflection\DocBlock docblock class */
    protected $docblock;

    /**
     * Factory method responsible for instantiating the correct sub type.
     *
     * @param string $tag_line The text for this tag, including description.
     *
     * @throws \InvalidArgumentException if an invalid tag line was presented.
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tag
     */
    public static function createInstance($tag_line)
    {
        if (!preg_match(
            '/^@([\w\-\_\\\\]+)(?:\s*([^\s].*)|$)?/us', $tag_line, $matches
        )) {
            throw new \InvalidArgumentException(
                'Invalid tag_line detected: ' . $tag_line
            );
        }

        // support hypphen separated tag names
        $tag_name = str_replace(
            ' ', '', ucwords(str_replace('-', ' ', $matches[1]))
        ).'Tag';
        $class_name = 'phpDocumentor\\Reflection\\DocBlock\\Tag\\' . $tag_name;

        return (@class_exists($class_name))
            ? new $class_name($matches[1], isset($matches[2]) ? $matches[2] : '')
            : new self($matches[1], isset($matches[2]) ? $matches[2] : '');
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
