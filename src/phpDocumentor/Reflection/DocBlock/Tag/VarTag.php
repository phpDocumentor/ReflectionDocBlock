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
 * Reflection class for a {@var} tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class VarTag extends ParamTag
{
    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'return')
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;
        $content = preg_split('/\s+/u', $content);

        if (count($content) == 0) {
            return;
        }

        // var always starts with the variable name
        $this->type = array_shift($content);

        // if the next item starts with a $ it must be the variable name
        if ((count($content) > 0)
            && (strlen($content[0]) > 0)
            && ($content[0][0] == '$')
        ) {
            $this->variableName = array_shift($content);
        }

        $this->description = implode(' ', $content);
    }
}
