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

namespace phpDocumentor\Reflection\DocBlock\Tag;

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @return tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ReturnTag extends Tag
{
    /** @var string */
    protected $type = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'return').
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;

        $content = preg_split('/[\ \t]+/u', $content, 2);

        // any output is considered a type
        $this->type = array_shift($content);

        $this->description = implode(' ', $content);
    }

    /**
     * Returns the unique types of the variable.
     *
     * @return string[]
     */
    public function getTypes()
    {
        $types = new \phpDocumentor\Reflection\DocBlock\Type\Collection(
            array($this->type),
            $this->docblock ? $this->docblock->getNamespace() : null,
            $this->docblock ? $this->docblock->getNamespaceAliases() : array()
        );

        return $types->getArrayCopy();
    }

    /**
     * Returns the type section of the variable.
     *
     * @return string
     */
    public function getType()
    {
        return implode('|', $this->getTypes());
    }
}
