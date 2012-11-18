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
     * Parses a tag and populates the member variables.
     *
     * @param string   $type     Tag identifier for this tag (should be 'return').
     * @param string   $content  Contents for this tag.
     * @param DocBlock $docblock The DocBlock which this tag belongs to.
     */
    public function __construct($type, $content, DocBlock $docblock = null)
    {
        parent::__construct($type, $content, $docblock);
        $content = preg_split('/\s+/u', $this->description, 2);

        // any output is considered a type
        $this->type = $content[0];

        $this->description = isset($content[1]) ? $content[1] : '';
    }

    /**
     * Returns the unique types of the variable.
     *
     * @return string[]
     */
    public function getTypes()
    {
        $this->refreshTypes();
        return $this->types->getArrayCopy();
    }

    /**
     * Returns the type section of the variable.
     *
     * @return string
     */
    public function getType()
    {
        $this->refreshTypes();
        return (string) $this->types;
    }
    
    /**
     * Parses the type, if needed.
     * 
     * @return void
     */
    protected function refreshTypes()
    {
        if (null === $this->types) {
            $this->types = new Collection(
                array($this->type),
                $this->docblock ? $this->docblock->getNamespace() : null,
                $this->docblock ? $this->docblock->getNamespaceAliases() : array()
            );
        }
    }
}
