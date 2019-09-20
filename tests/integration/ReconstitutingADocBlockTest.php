<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
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
    public function tearDown(): void
    {
        m::close();
    }

    public function testReconstituteADocBlock(): void
    {
        /** @var string $docComment */
        $docComment;
        /** @var string $reconstitutedDocComment */
        $reconstitutedDocComment;

        include(__DIR__ . '/../../examples/03-reconstituting-a-docblock.php');

        $this->assertSame($docComment, $reconstitutedDocComment);
    }
}
