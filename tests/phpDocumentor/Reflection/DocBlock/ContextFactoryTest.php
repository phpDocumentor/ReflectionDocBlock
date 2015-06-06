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

namespace phpDocumentor\Reflection\DocBlock {

// Added imports on purpose as mock for the unit tests, please do not remove.
    use Mockery as m;
    use phpDocumentor\Reflection\DocBlock,
        phpDocumentor\Reflection\DocBlock\Tag;
    use \ReflectionClass; // yes, the slash is part of the test

    /**
     * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\ContextFactory
     * @covers ::<private>
     */
    class ContextFactoryTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @covers ::createFromClassReflector
         * @covers ::createForNamespace
         * @uses phpDocumentor\Reflection\DocBlock\Context
         */
        public function testReadsNamespaceFromClassReflection()
        {
            $fixture = new ContextFactory();
            $context = $fixture->createFromClassReflector(new ReflectionClass($this));

            $this->assertSame(__NAMESPACE__, $context->getNamespace());
        }

        /**
         * @covers ::createFromClassReflector
         * @covers ::createForNamespace
         * @uses phpDocumentor\Reflection\DocBlock\Context
         */
        public function testReadsAliasesFromClassReflection()
        {
            $fixture = new ContextFactory();
            $expected = [
                'm' => 'Mockery',
                'DocBlock' => 'phpDocumentor\Reflection\DocBlock',
                'Tag' => 'phpDocumentor\Reflection\DocBlock\Tag',
                'ReflectionClass' => 'ReflectionClass'
            ];
            $context = $fixture->createFromClassReflector(new ReflectionClass($this));

            $this->assertSame($expected, $context->getNamespaceAliases());
        }

        /**
         * @covers ::createForNamespace
         * @uses phpDocumentor\Reflection\DocBlock\Context
         */
        public function testReadsNamespaceFromProvidedNamespaceAndContent()
        {
            $fixture = new ContextFactory();
            $context = $fixture->createForNamespace(__NAMESPACE__, file_get_contents(__FILE__));

            $this->assertSame(__NAMESPACE__, $context->getNamespace());
        }

        /**
         * @covers ::createForNamespace
         * @uses phpDocumentor\Reflection\DocBlock\Context
         */
        public function testReadsAliasesFromProvidedNamespaceAndContent()
        {
            $fixture = new ContextFactory();
            $expected = [
                'm' => 'Mockery',
                'DocBlock' => 'phpDocumentor\Reflection\DocBlock',
                'Tag' => 'phpDocumentor\Reflection\DocBlock\Tag',
                'ReflectionClass' => 'ReflectionClass'
            ];
            $context = $fixture->createForNamespace(__NAMESPACE__, file_get_contents(__FILE__));

            $this->assertSame($expected, $context->getNamespaceAliases());
        }
    }
}

namespace phpDocumentor\Reflection\DocBlock\Mock {
    // the following import should not show in the tests above
    use phpDocumentor\Reflection\DocBlock\Description;
}
