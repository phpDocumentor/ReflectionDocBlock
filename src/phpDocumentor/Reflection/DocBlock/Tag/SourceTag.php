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

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @source tag in a Docblock.
 *
 * @author  Vasil Rangelov <boen.robot@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class SourceTag extends Tag
{
    /**
     * @var int The starting line, relative to the structural element's
     *     location.
     */
    protected $startingLine = 1;

    /** 
     * @var int|null The number of lines, relative to the starting line. NULL
     *     means "to the end".
     */
    protected $lineCount = null;

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string   $type     Tag identifier for this tag (should be 'source').
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
        if (preg_match(
            '/^
                # Starting line
                ([1-9]\d*)
                \s*
                # Number of lines
                (?:
                    ((?1))
                    \s+
                )?
                # Description
                (.*)
            $/sux',
            $this->description,
            $matches
        )) {
            $this->startingLine = (int)$matches[1];
            if (isset($matches[2]) && '' !== $matches[2]) {
                $this->lineCount = (int)$matches[2];
            }
            $this->description = $matches[3];
        }
    }

    /**
     * Returns the starting line.
     *
     * @return int The starting line, relative to the structural element's
     *     location.
     */
    public function getStartingLine()
    {
        return $this->startingLine;
    }

    /**
     * Returns the number of lines.
     *
     * @return int|null The number of lines, relative to the starting line. NULL
     *     means "to the end".
     */
    public function getLineCount()
    {
        return $this->lineCount;
    }
}
