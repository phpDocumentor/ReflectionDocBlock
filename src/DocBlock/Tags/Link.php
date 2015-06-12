<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Ben Selby <benmatselby@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @link tag in a Docblock.
 */
class Link extends Tag
{
    /** @var string */
    protected $link = '';

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->description) {
            $this->description = "{$this->link} {$this->description}";
        }

        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        parent::setContent($content);
        $parts = preg_split('/\s+/Su', $this->description, 2);

        $this->link = $parts[0];

        $this->setDescription(isset($parts[1]) ? $parts[1] : $parts[0]);

        $this->description = $content;
        return $this;
    }

    /**
    * Gets the link
    *
    * @return string
    */
    public function getLink()
    {
        return $this->link;
    }

    /**
    * Sets the link
    *
    * @param string $link The link
    *
    * @return $this
    */
    public function setLink($link)
    {
        $this->link = $link;

        $this->description = null;
        return $this;
    }
}
