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

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @param tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ParamTag extends ReturnTag
{
    /**
     * @var string
     */
    protected $variableName = '';

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->content
                = "{$this->type} {$this->variableName} {$this->description}";
        }
        return $this->content;
    }
    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        Tag::setContent($content);
        $content = preg_split(
            '/(\s+)/Su',
            $this->description,
            3,
            PREG_SPLIT_DELIM_CAPTURE
        );

        // if the first item that is encountered is not a variable; it is a type
        if (isset($content[0])
            && (strlen($content[0]) > 0)
            && ($content[0][0] !== '$')
        ) {
            $this->type = array_shift($content);
            array_shift($content);
        }

        // if the next item starts with a $ it must be the variable name
        if (isset($content[0])
            && (strlen($content[0]) > 0)
            && ($content[0][0] == '$')
        ) {
            $this->variableName = array_shift($content);
            array_shift($content);
        }

        $this->description = implode('', $content);

        return $this;
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
     * @return $this
     */
    public function setVariableName($name)
    {
        $this->content = null;
        $this->variableName = $name;

        return $this;
    }
}
