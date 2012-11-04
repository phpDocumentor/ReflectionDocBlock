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
 * Reflection class for a {@param} tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ParamTag extends Tag
{
    /** @var string */
    protected $type = '';

    /**
     * @var string
     */
    protected $variableName = null;

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'param')
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;
        $content = preg_split('/\s+/u', $content);

        // if the first item that is encountered is not a variable; it is a type
        if (isset($content[0])
            && (strlen($content[0]) > 0)
            && ($content[0][0] !== '$')
        ) {
            $this->type = array_shift($content);
        }

        // if the next item starts with a $ it must be the variable name
        if (isset($content[0])
            && (strlen($content[0]) > 0)
            && ($content[0][0] == '$')
        ) {
            $this->variableName = array_shift($content);
        }

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

    /**
     * Returns the variable's name.
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * Sets the variable's name.
     *
     * @param string $name The new name for this variable.
     *
     * @return void
     */
    public function setVariableName($name)
    {
        $this->variableName = $name;
    }
}
