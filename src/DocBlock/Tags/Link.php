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

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;

/**
 * Reflection class for a @link tag in a Docblock.
 */
final class Link extends BaseTag
{
    /** @var string */
    private $link = '';

    public function __construct($link, Description $description)
    {
        $this->link = $link;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public static function create($body, DescriptionFactory $descriptionFactory = null, Context $context = null)
    {
        $parts = preg_split('/\s+/Su', $body, 2);

        $link = $parts[0];
        $description = $descriptionFactory->create(isset($parts[1]) ? $parts[1] : '', $context);

        return new static($link, $description);
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

    public function __toString()
    {
        return $this->link . ' ' . $this->description;
    }
}
