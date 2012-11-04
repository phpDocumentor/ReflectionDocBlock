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
 * Reflection class for an @author tag in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class AuthorTag extends Tag
{
    /** @var string The name of the author */
    protected $name = '';
    
    /** @var string The email of the author */
    protected $email = '';
    
    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Name of the tag.
     * @param string $content The contents of the given tag.
     */
    public function __construct($type, $content)
    {
        parent::__construct($type, $content);
        if (preg_match('/^([^\<]*)(\<([^\>]*)\>)?$/', $content, $matches)) {
            $this->name = trim($matches[1]);
            if (isset($matches[3])) {
                $this->email = trim($matches[3]);
            }
        }
    }
    
    /**
     * Gets the author's name.
     * 
     * @return string The author's name.
     */
    public function getAuthorName()
    {
        return $this->name;
    }
    
    /**
     * Gets the author's email.
     * 
     * @return string The author's email.
     */
    public function getAuthorEmail()
    {
        return $this->email;
    }
}
