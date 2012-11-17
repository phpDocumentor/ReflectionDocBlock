<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @example tag in a Docblock.
 *
 * @author  Vasil Rangelov <boen.robot@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class ExampleTag extends SourceTag
{
    /** @var string Path to a file to use as an example. Can also be an URI. */
    protected $filePath = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string   $type     Tag identifier for this tag (should be 'example').
     * @param string   $content  Contents for this tag.
     * @param DocBlock $docblock The DocBlock which this tag belongs to.
     */
    public function __construct($type, $content, DocBlock $docblock = null)
    {
        Tag::__construct($type, $content, $docblock);
        if (preg_match(
            '/^(?:\"([^\"]+)\"|(\S+))(?:\s+(.*))?$/su',
            $this->description,
            $matches
        )) {
            if ('' !== $matches[1]) {
                //Quoted file path.
                $this->filePath = trim($matches[1]);
            } elseif (false === strpos($matches[2], ':')) {
                //Relative URL or a file path with no spaces in it.
                $this->filePath = rawurldecode(
                    str_replace(array('/', '\\'), '%2F', $matches[2])
                );
            } else {
                //Absolute URL or URI.
                $this->filePath = $matches[2];
            }
            
            if (isset($matches[3])) {
                parent::__construct($type, $matches[3]);
                $this->content = $content;
            } else {
                $this->description = '';
            }
        }
    }

    /**
     * Returns the file path.
     *
     * @return string Path to a file to use as an example. Can also be an URI.
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
