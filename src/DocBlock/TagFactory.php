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

use phpDocumentor\Reflection\Types\Context;

interface TagFactory
{
    public function addParameter($name, $value);

    public function addService($service);

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
    public function create($tagLine, Context $context = null);

    /**
     * Registers a handler for tags.
     *
     * Registers a handler for tags. The class specified is autoloaded if it's not available. It must inherit from
     * this class.
     *
     * @param string $tag          Name of tag to register a handler for. When registering a namespaced tag, the full
     *                             name, along with a prefixing slash MUST be provided.
     * @param string|null $handler FQCN of handler.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    public function registerTagHandler($tag, $handler);
}
