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

use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;

final class StandardTagFactory implements TagFactory
{
    /** PCRE regular expression matching a tag name. */
    const REGEX_TAGNAME = '[\w\-\_\\\\]+';

    /**
     * @var array An array with a tag as a key, and an FQCN to a class that handles it as an array value.
     */
    private $tagHandlerMappings = array(
        'author'         => '\phpDocumentor\Reflection\DocBlock\Tags\Author',
        'covers'         => '\phpDocumentor\Reflection\DocBlock\Tags\Covers',
        'deprecated'     => '\phpDocumentor\Reflection\DocBlock\Tags\Deprecated',
        'example'        => '\phpDocumentor\Reflection\DocBlock\Tags\Example',
        'link'           => '\phpDocumentor\Reflection\DocBlock\Tags\Link',
        'method'         => '\phpDocumentor\Reflection\DocBlock\Tags\Method',
        'param'          => '\phpDocumentor\Reflection\DocBlock\Tags\Param',
        'property-read'  => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyRead',
        'property'       => '\phpDocumentor\Reflection\DocBlock\Tags\Property',
        'property-write' => '\phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite',
        'return'         => '\phpDocumentor\Reflection\DocBlock\Tags\Return',
        'see'            => '\phpDocumentor\Reflection\DocBlock\Tags\See',
        'since'          => '\phpDocumentor\Reflection\DocBlock\Tags\Since',
        'source'         => '\phpDocumentor\Reflection\DocBlock\Tags\Source',
        'throw'          => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'throws'         => '\phpDocumentor\Reflection\DocBlock\Tags\Throws',
        'uses'           => '\phpDocumentor\Reflection\DocBlock\Tags\Uses',
        'var'            => '\phpDocumentor\Reflection\DocBlock\Tags\Var_',
        'version'        => '\phpDocumentor\Reflection\DocBlock\Tags\Version'
    );

    /** @var FqsenResolver */
    private $fqsenResolver;

    /** @var mixed[] */
    private $serviceLocator = [];

    public function __construct(FqsenResolver $fqsenResolver)
    {
        $this->fqsenResolver = $fqsenResolver;
        $this->addService($fqsenResolver);
    }

    public function addParameter($name, $value)
    {
        $this->serviceLocator[$name] = $value;
    }

    public function addService($service)
    {
        $this->serviceLocator[get_class($service)] = $service;
    }

    /**
     * Factory method responsible for instantiating the correct sub type.
     *
     * @param string  $tagLine The text for this tag, including description.
     * @param Context $context
     *
     * @throws \InvalidArgumentException if an invalid tag line was presented.
     *
     * @return static A new tag object.
     */
    public function create($tagLine, Context $context = null)
    {
        if (! $context) {
            $context = new Context('');
        }
        list($tagName, $tagBody) = $this->extractTagParts($tagLine);

        $handler = Generic::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handler = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            $tagName = (string)$this->fqsenResolver->resolve($tagName, $context);
            if (isset($this->tagHandlerMappings[$tagName])) {
                $handler = $this->tagHandlerMappings[$tagName];
            }
        }

        $parameters = (new \ReflectionMethod($handler, 'create'))->getParameters();

        $wiring = array_merge(
            $this->serviceLocator,
            [
                'name'         => $tagName,
                'body'         => $tagBody,
                Context::class => $context
            ]
        );

        $arguments = [];
        foreach ($parameters as $index => $parameter) {
            $typeHint = $parameter->getClass() ? $parameter->getClass()->getName() : null;
            if (isset($wiring[$typeHint])) {
                $arguments[] = $wiring[$typeHint];
                continue;
            }

            $parameterName = $parameter->getName();
            if (isset($wiring[$parameterName])) {
                $arguments[] = $wiring[$parameterName];
                continue;
            }

            $arguments[] = null;
        }

        return call_user_func_array([$handler, 'create'], $arguments);
    }

    /**
     * Registers a handler for tags.
     *
     * Registers a handler for tags. The class specified is autoloaded if it's not available. It must inherit from
     * this class.
     *
     * @param string      $tag     Name of tag to register a handler for. When registering a namespaced tag, the full
     *                             name, along with a prefixing slash MUST be provided.
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
            && ! strpos($tag, '\\') //Accept no slash, and 1st slash at offset 0.
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
        if (! preg_match('/^@(' . self::REGEX_TAGNAME . ')(?:\s*([^\s].*)|$)?/us', $tagLine, $matches)) {
            throw new \InvalidArgumentException(
                'The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors'
            );
        }

        if (count($matches) < 3) {
            $matches[] = '';
        }

        return array_slice($matches, 1);
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
