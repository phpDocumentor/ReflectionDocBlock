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

use phpDocumentor\Reflection\DocBlock\Description\Formatter;
use phpDocumentor\Reflection\DocBlock\Description\PassthroughFormatter;


class Description
{
    /** @var Tag[]|string[] The contents, as an array of strings and Tag objects */
    private $tokens;

    /**
     * Initializes a this object with a series of tokens of which a description consists.
     *
     * @param Tag[]|string[] $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Renders this description as a string where the provided formatter will format tags for the expected output.
     *
     * @param Formatter|null $formatter
     *
     * @return string
     */
    public function render(Formatter $formatter = null)
    {
        if ($formatter === null) {
            $formatter = new PassthroughFormatter();
        }

        return $formatter->format($this->tokens);
    }

}
