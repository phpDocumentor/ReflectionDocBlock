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

use phpDocumentor\Reflection\Type;

class Array_ implements Type
{
    /** @var Type */
    private $valueType;

    /** @var Type */
    private $keyType;

    public function __construct(Type $valueType = null, Type $keyType = null)
    {
        if ($keyType === null) {
            $keyType = new Mixed();
        }
        if ($valueType === null) {
            $valueType = new Mixed();
        }

        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }

    /**
     * @return Type
     */
    public function getKeyType()
    {
        return $this->keyType;
    }

    /**
     * @return Type
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    public function __toString()
    {
        if ($this->valueType instanceof Mixed) {
            return 'array';
        }

        return $this->valueType . '[]';
    }
}
