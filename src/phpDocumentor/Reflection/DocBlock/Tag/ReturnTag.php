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
use phpDocumentor\Reflection\DocBlock\Type\Collection;

/**
 * Reflection class for a @return tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ReturnTag extends Tag
{
    /** @var string The raw type component. */
    protected $type = '';
    
    /** @var Collection The parsed type component. */
    protected $types = null;

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->type} {$this->description}";
        }

        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        parent::setContent($content);

        $parts = preg_split('/\s+/Su', $this->description, 2);

        // any output is considered a type
        $this->type = $parts[0];
        $this->types = null;

        $this->setDescription(isset($parts[1]) ? $parts[1] : '');

        $this->content = $content;
        return $this;
    }

    /**
     * Returns the unique parsed types of the variable.
     *
     * @return string[]
     */
    public function getTypes()
    {
        return $this->getTypesCollection()->getArrayCopy();
    }

    /**
     * Returns the parsed type section of the variable.
     *
     * @return string
     */
    public function getType()
    {
        return (string) $this->getTypesCollection();
    }

    /**
     * Set the type section of the variable
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->types = null;
        $this->content = null;
        return $this;
    }

    /**
     * Returns the type collection.
     * 
     * @return Collection
     */
    protected function getTypesCollection()
    {
        if (null === $this->types) {
            $this->types = new Collection(
                array($this->type),
                $this->docblock ? $this->docblock->getContext() : null
            );
        }
        return $this->types;
    }
}
