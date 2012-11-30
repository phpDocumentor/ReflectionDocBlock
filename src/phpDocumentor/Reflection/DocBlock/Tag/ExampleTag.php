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
    /** 
     * @var string Path to a file to use as an example.
     *     May also be an absolute URI.
     */
    protected $filePath = '';

    public function getContent()
    {
        if (null === $this->content) {
            $this->content
                = (preg_match('/\s/Su', $this->filePath)
                    ? '"' . $this->filePath . '"'
                    : $this->filePath) . ' ' . $this->getContent();
        }

        return $this->content;
    }
    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        Tag::setContent($content);
        if (preg_match(
            '/^
                # File component
                (?:
                    # File path in quotes
                    \"([^\"]+)\"
                    |
                    # File URI
                    (\S+)
                )
                # Remaining content (parsed by SourceTag)
                (?:\s+(.*))?
            $/sux',
            $this->description,
            $matches
        )) {
            $this->setFilePath('' === $matches[1] ? $matches[2] : $matches[1]);

            if (isset($matches[3])) {
                parent::setContent($matches[3]);
                $this->content = $content;
            } else {
                $this->description = '';
            }
        }

        return $this;
    }

    /**
     * Returns the file path.
     *
     * @return string Path to a file to use as an example.
     *     May also be an absolute URI.
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
    
    /**
     * Sets the file path.
     * 
     * @param string $filePath The new file path or URI to use as an example.
     * 
     * @return $this
     */
    public function setFilePath($filePath)
    {
        if (preg_match('/\s/Su', $filePath)) {
            //Quoted file path.
            $this->filePath = trim($filePath);
        } elseif (false === strpos($filePath, ':')) {
            //Relative URL or a file path with no spaces in it.
            $this->filePath = rawurldecode(
                str_replace(array('/', '\\'), '%2F', $filePath)
            );
        } else {
            //Absolute URL or URI.
            $this->filePath = $filePath;
        }

        return $this;
    }
}
