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

namespace phpDocumentor\Reflection;

use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ReconstitutingADocBlockTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown()
    {
        m::close();
    }

    public function testReconstituteADocBlock()
    {
        /**
         * @var string $docComment
         * @var string $reconstitutedDocComment
         */
        include(__DIR__ . '/../../examples/03-reconstituting-a-docblock.php');

        $this->assertSame($docComment, $reconstitutedDocComment);
    }
}
