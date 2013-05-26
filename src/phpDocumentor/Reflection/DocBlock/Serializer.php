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

namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;

/**
 * Serializes a DocBlock instance
 *
 * @author  Barry vd. Heuvel <barryvdh@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class Serializer
{

    /** @var string The string to indent the comment with */
    protected $indentString;

    /** @var  int The number of times $indentString is repeated */
    protected $indent;

    /** @var  bool Indent the first line */
    protected $indentFirstLine;

    /** @var int The max length of a description line. */
    protected $lineLength = null;

    /**
     * Create a Serializer instance.
     *
     * @param string $indentString
     * @param int $indent
     * @param bool $indentFirstLine
     * @internal param string $indentationString The indentation string.
     */
    public function __construct($indentString = ' ', $indent = 4, $indentFirstLine = true)
    {
        $this->indentString = $indentString;
        $this->indent = $indent;
        $this->indentFirstLine = $indentFirstLine;
    }

    /**
     * @param $indentationString
     * @return $this
     */
    public function setIndentationString($indentationString)
    {
        $this->indentation = $indentationString;
        return $this;
    }

    /**
     * @param $indent
     * @return $this
     */
    public function setIndent($indent){
        $this->indent = $indent;
        return $this;
    }

    /**
     * @param $indentFirstLine
     * @return $this
     */
    public function setIndentFirstLine($indentFirstLine){
        $this->indentFirstLine = $indentFirstLine;
        return $this;
    }

    /**
     * @param $lineLength
     * @return $this
     */
    public function setLineLength($lineLength){
        $this->lineLength = $lineLength;
        return $this;
    }

    /**
     * Generate a DocBlock Comment
     *
     * @param DocBlock  The DocBlock to serialize
     * @return string
     */
    public function getDocComment($phpdoc){

        $indent = '';
        for($i=0;$i<$this->indent;$i++){
            $indent .= $this->indentString;
        }
        $firstIndent = $this->indentFirstLine ? $indent : '';

        $description = $phpdoc->getText();
        if($this->lineLength){
            $description = wordwrap($description, $this->lineLength);
        }
        $description = str_replace("\n", "\n$indent * ", $description);

        $comment = "$firstIndent/**\n$indent * $description\n$indent *\n";

        /** @var Tag $tag */
        foreach ($phpdoc->getTags() as $tag) {
            $comment .= $indent.' * @'. $tag->getName() . " " . $tag->getContent() . "\n";
        }

        $comment .= $indent.' */';

        return $comment;
    }
}
