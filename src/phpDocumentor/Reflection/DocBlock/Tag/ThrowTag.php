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

/**
 * Reflection class for a mistyped @throws tag called @throw in a Docblock.
 *
 * This is a very common error, so @throw is aliased to be @throws
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ThrowTag extends ThrowsTag
{
    /**
     * Sets the type to {@throws} and lets parent parse the tag and populates the
     * member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'return')
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        if ('throw' !== $type) {
            throw new \InvalidArgumentException(
                'Internal error, ' . __CLASS__ . ' was called with ' . $type
            );
        }

        parent::__construct('throws', $content);
    }
}
