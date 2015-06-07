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


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;

final class Compound implements Type
{
    /** @var Type[] */
    private $types = [];

    /**
     * @param Type[]|Fqsen[] $types
     */
    public function __construct($types)
    {
        $this->types = $types;
    }

    /**
     * @param integer $index
     *
     * @return null|Type|Fqsen
     */
    public function get($index)
    {
        if (!$this->has($index)) {
            return null;
        }

        return $this->types[$index];
    }

    /**
     * @param integer $index
     *
     * @return bool
     */
    public function has($index)
    {
        return isset($this->types[$index]);
    }

    public function __toString()
    {
        return implode('|', $this->types);
    }
}
