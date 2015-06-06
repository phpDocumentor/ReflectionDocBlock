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

use Mockery as m;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Context
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getNamespace
     */
    public function testProvidesANormalizedNamespace()
    {
        $fixture = new Context('\My\Space');
        $this->assertSame('My\Space', $fixture->getNamespace());
    }

    /**
     * @covers ::__construct
     * @covers ::getNamespace
     */
    public function testInterpretsNamespaceNamedGlobalAsRootNamespace()
    {
        $fixture = new Context('global');
        $this->assertSame('', $fixture->getNamespace());
    }

    /**
     * @covers ::__construct
     * @covers ::getNamespace
     */
    public function testInterpretsNamespaceNamedDefaultAsRootNamespace()
    {
        $fixture = new Context('default');
        $this->assertSame('', $fixture->getNamespace());
    }

    /**
     * @covers ::__construct
     * @covers ::getNamespaceAliases
     */
    public function testProvidesNormalizedNamespaceAliases()
    {
        $fixture = new Context('', ['Space' => '\My\Space']);
        $this->assertSame(['Space' => 'My\Space'], $fixture->getNamespaceAliases());
    }
}
