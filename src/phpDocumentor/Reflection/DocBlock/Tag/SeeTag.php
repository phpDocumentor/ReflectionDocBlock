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
 * Reflection class for a @see tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class SeeTag extends Tag
{
    /** @var string */
    protected $refers = null;

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string   $type     Tag identifier for this tag (should be 'see').
     * @param string   $content  Contents for this tag.
     * @param DocBlock $docblock The DocBlock which this tag belongs to.
     * @param Location $location Location of the tag.
     */
    public function __construct(
        $type,
        $content,
        DocBlock $docblock = null,
        Location $location = null
    ) {
        parent::__construct($type, $content, $docblock, $location);
        $content = preg_split('/\s+/u', $content);

        // any output is considered a type
        $this->refers = array_shift($content);

        $this->description = implode(' ', $content);
    }

    /**
     * Returns the type of the variable.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->refers;
    }
}
