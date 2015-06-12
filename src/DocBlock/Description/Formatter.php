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

namespace phpDocumentor\Reflection\DocBlock\Description;

use phpDocumentor\Reflection\DocBlock\Tag;

interface Formatter
{
    /**
     * Formats the given series of tokens so to form a readable string and return that.
     *
     * @param string[]|Tag[] $tokens
     *
     * @return string
     */
    public function format(array $tokens);
}
