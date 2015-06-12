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

use phpDocumentor\Reflection\FqsenFactory;

final class TagFactory
{
    /** PCRE regular expression matching a tag name. */
    const REGEX_TAGNAME = '[\w\-\_\\\\]+';

    /**
     * @var array An array with a tag as a key, and an FQCN to a class that handles it as an array value.
     */
    private $tagHandlerMappings = array(
        'author' => '\phpDocumentor\Reflection\DocBlock\Tags\Author',
        'covers' => '\phpDocumentor\Reflection\DocBlock\Tags\Covers',
        'deprecated' => '\phpDocumentor\Reflection\DocBlock\Tags\Deprecated',
        'example' => '\phpDocumentor\Reflection\DocBlock\Tags\Example',
        'link' => '\phpDocumentor\Reflection\DocBlock\Tags\Link',
        'method' => '\phpDocumentor\Reflection\DocBlock\Tags\Method',
        'param' => '\phpDocumentor\Reflection\DocBlock\Tags\Param',
        'property-read' => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyRead',
        'property' => '\phpDocumentor\Reflection\DocBlock\Tags\Property',
        'property-write' => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite',
        'return' => '\phpDocumentor\Reflection\DocBlock\Tags\Return',
        'see' => '\phpDocumentor\Reflection\DocBlock\Tags\See',
        'since' => '\phpDocumentor\Reflection\DocBlock\Tags\Since',
        'source' => '\phpDocumentor\Reflection\DocBlock\Tags\Source',
        'throw' => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'throws' => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'uses' => '\phpDocumentor\Reflection\DocBlock\Tags\Uses',
        'var' => '\phpDocumentor\Reflection\DocBlock\Tags\Var_',
        'version' => '\phpDocumentor\Reflection\DocBlock\Tags\Version'
    );

    /** @var FqsenFactory */
    private $fqsenFactory;

    public function __construct(FqsenFactory $fqsenFactory)
    {
        $this->fqsenFactory = $fqsenFactory;
    }

    /**
     * Factory method responsible for instantiating the correct sub type.
     *
     * @param string $tagLine The text for this tag, including description.
     * @param Context $context
     *
     * @throws \InvalidArgumentException if an invalid tag line was presented.
     *
     * @return static A new tag object.
     */
    public function create($tagLine, Context $context = null)
    {
        if (!$context) {
            $context = new Context('');
        }
        list($tagName, $tagDescription) = $this->extractTagParts($tagLine);

        $handler = Tag::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handler = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            $tagName = (string)$this->fqsenFactory->create($tagName, $context);
            if (isset($this->tagHandlerMappings[$tagName])) {
                $handler = $this->tagHandlerMappings[$tagName];
            }
        }

        return $handler::create($tagName, $tagDescription);
    }

    /**
     * Registers a handler for tags.
     *
     * Registers a handler for tags. The class specified is autoloaded if it's not available. It must inherit from
     * this class.
     *
     * @param string $tag Name of tag to register a handler for. When registering a namespaced tag, the full
     *     name, along with a prefixing slash MUST be provided.
     * @param string|null $handler FQCN of handler.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    public function registerTagHandler($tag, $handler)
    {
        $tag = trim((string)$tag);

        if ('' !== $tag
            && class_exists($handler)
            && is_subclass_of($handler, Tag::class)
            && !strpos($tag, '\\') //Accept no slash, and 1st slash at offset 0.
        ) {
            $this->tagHandlerMappings[$tag] = $handler;
            return true;
        }

        return false;
    }

    /**
     * Extracts all components for a tag.
     *
     * @param string $tagLine
     *
     * @return string[]
     */
    private function extractTagParts($tagLine)
    {
        $matches = array();
        if (!preg_match('/^@(' . self::REGEX_TAGNAME . ')(?:\s*([^\s].*)|$)?/us', $tagLine, $matches)) {
            throw new \InvalidArgumentException(
                'The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors'
            );
        }

        if (count($matches) == 1) {
            $matches[] = '';
        }

        return $matches;
    }

    private function isAnnotation($tag)
    {
        // 1. Contains a namespace separator
        // 2. Contains parenthesis
        // 3. Is present in a list of known annotations (make the algorithm smart by first checking is the last part
        //    of the annotation class name matches the found tag name

        return false;
    }
}
