<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;

/**
 * Parses a tag definition for a DocBlock.
 */
class Tag
{
    /** @var string Name of the tag */
    protected $name = '';

    /** @var Description|null Description of the tag. */
    protected $description;

    /**
     * Parses a tag and populates the member variables.
     *
     * We explicitly do not type-hint the $description so that classes inheriting this class can override the
     * constructor without running into PHP notices.
     *
     * @param string $name Name of the tag.
     * @param Description $description The contents of the given tag.
     */
    public function __construct($name, $description)
    {
        $this->validateTagName($name);
        if (!$description instanceof Description) {
            throw new \InvalidArgumentException('The description should be an object of type Description');
        }

        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Gets the name of this tag.
     *
     * @return string The name of this tag.
     */
    public function getName()
    {
        return $this->name;
    }

    public function render(DocBlock\Description\Formatter $formatter = null)
    {
        if (!$formatter) {
            $formatter = new DocBlock\Description\PassthroughFormatter();
        }

        return $formatter->format([$this]);
    }

    /**
     * Returns the tag as a serialized string
     *
     * @return string
     */
    public function __toString()
    {
        return "@{$this->getName()} {$this->description->render()}";
    }

    /**
     * Validates if the tag name matches the expected format, otherwise throws an exception.
     *
     * @param string $name
     *
     * @return void
     */
    private function validateTagName($name)
    {
        if (!preg_match('/^' . TagFactory::REGEX_TAGNAME . '$/u', $name)) {
            throw new \InvalidArgumentException(
                'The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, '
                . 'hyphens and backslashes.'
            );
        }
    }
}
