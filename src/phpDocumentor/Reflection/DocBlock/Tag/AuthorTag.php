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

    /** @var array Array of URIs belonging to the author, including email */
    protected $uris = array();
    
    /** @var string The role of the author */
    protected $role = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'author').
     * @param string $content The contents of the given tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;
        if (preg_match(
                '/^
                    # Name
                    ([^\<]*)
                    (?:
                        # URIs
                        \<([^>]*)\>\s*
                        # Role
                        (?:
                          \(([^\)]*)\) 
                        )?
                        # Description
                        (.*)
                    )?
                $/sux',
                $content,
                $matches
            )
        ) {
            $this->name = trim($matches[1]);
            if (isset($matches[2])) {
                $matches[2] = trim($matches[2]);
                if ('' !== $matches[2]) {
                    $this->uris = preg_split('/\s+/u', $matches[2]);
                }
            }
            $this->role        = isset($matches[3]) ? trim($matches[3]) : '';
            $this->description = isset($matches[4]) ? trim($matches[4]) : '';
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
     * Gets the author's URIs.
     * 
     * @return array Array of URIs belonging to the author, including email.
     */
    public function getAuthorURIs()
    {
        return $this->uris;
    }

    /**
     * Gets the author's role.
     * 
     * @return string The role of the author.
     */
    public function getAuthorRole()
    {
        return $this->role;
    }
}
