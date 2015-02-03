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

    /**
     * @var bool Whether the file path component represents an URI.
     *     This determines how the file portion appears at {@link getContent()}.
     */
    protected $isURI = false;

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->content) {
            $filePath = '"' . $this->filePath . '"';
            if ($this->isURI) {
                $filePath = $this->isUriRelative($this->filePath)
                    ? str_replace('%2F', '/', rawurlencode($this->filePath))
                    :$this->filePath;
            }

            $this->content = $filePath . ' ' . parent::getContent();
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
            if ('' !== $matches[1]) {
                $this->setFilePath($matches[1]);
            } else {
                $this->setFileURI($matches[2]);
            }

            if (isset($matches[3])) {
                parent::setContent($matches[3]);
            } else {
                $this->setDescription('');
            }
            $this->content = $content;
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
     * @param string $filePath The new file path to use for the example.
     * 
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->isURI = false;
        $this->filePath = trim($filePath);

        $this->content = null;
        return $this;
    }
    
    /**
     * Sets the file path as an URI.
     * 
     * This function is equivalent to {@link setFilePath()}, except that it
     * converts an URI to a file path before that.
     * 
     * There is no getFileURI(), as {@link getFilePath()} is compatible.
     * 
     * @param string $uri The new file URI to use as an example.
     *
     * @return $this
     */
    public function setFileURI($uri)
    {
        $this->isURI   = true;
        $this->content = null;

        $this->filePath = $this->isUriRelative($uri)
            ? rawurldecode(str_replace(array('/', '\\'), '%2F', $uri))
            : $this->filePath = $uri;

        return $this;
    }

    /**
     * Returns true if the provided URI is relative or contains a complete scheme (and thus is absolute).
     *
     * @param string $uri
     *
     * @return bool
     */
    private function isUriRelative($uri)
    {
        return false === strpos($uri, ':');
    }
}
