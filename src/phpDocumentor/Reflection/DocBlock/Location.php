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

namespace phpDocumentor\Reflection\DocBlock;

/**
 * The location a DocBlock occurs within a file.
 *
 * @author  Vasil Rangelov <boen.robot@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class Location
{
    /** @var int Line where the DocBlock text starts. */
    protected $line_number = 0;

    /** @var int Column where the DocBlock text starts. */
    protected $column_number = 0;
    
    public function __construct(
        $line_number = 0,
        $column_number = 0
    ) {
        $this->line_number = (int)$line_number;
        $this->column_number = (int)$column_number;
    }

    /**
     * @return int Line where the DocBlock text starts.
     */
    public function getLineNumber()
    {
        return $this->line_number;
    }

    /**
     * @return int Column where the DocBlock text starts.
     */
    public function getColumnNumber()
    {
        return $this->column_number;
    }
}
