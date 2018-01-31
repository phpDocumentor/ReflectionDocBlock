<?php declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2018 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Parses a tag definition for a DocBlock.
 */
class Generic extends BaseTag implements Factory\StaticMethod
{
    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $name Name of the tag.
     * @param Description $description The contents of the given tag.
     */
    public function __construct(string $name, ?Description $description = null)
    {
        $this->validateTagName($name);

        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Creates a new tag that represents any unknown tag type.
     *
     *
     * @return static
     */
    public static function create(
        string $body,
        string $name = '',
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ): self {
        Assert::stringNotEmpty($name);
        Assert::notNull($descriptionFactory);

        $description = $descriptionFactory && $body ? $descriptionFactory->create($body, $context) : null;

        return new static($name, $description);
    }

    /**
     * Returns the tag as a serialized string
     */
    public function __toString(): string
    {
        return $this->description ? $this->description->render() : '';
    }

    /**
     * Validates if the tag name matches the expected format, otherwise throws an exception.
     */
    private function validateTagName(string $name): void
    {
        if (! preg_match('/^' . StandardTagFactory::REGEX_TAGNAME . '$/u', $name)) {
            throw new \InvalidArgumentException(
                'The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, '
                . 'hyphens and backslashes.'
            );
        }
    }
}
