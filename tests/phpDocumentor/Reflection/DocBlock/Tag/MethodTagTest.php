<?php
/**
 * phpDocumentor Method Tag Test
 * 
 * PHP version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Test class for \phpDocumentor\Reflection\DocBlock\Tag\MethodTag
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius. (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
class MethodTagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $signature       The signature to test.
     * @param bool   $valid           Whether the given signature is expected to
     *     be valid.
     * @param string $expected_name   The method name that is expected from this
     *     signature.
     * @param string $expected_return The return type that is expected from this
     *     signature.
     * @param bool   $paramCount      Number of parameters in the signature.
     * @param string $description     The short description mentioned in the
     *     signature.
     * 
     * @covers \phpDocumentor\Reflection\DocBlock\Tag\MethodTag
     * @dataProvider getTestSignatures
     *
     * @return void
     */
    public function testConstruct(
        $signature,
        $valid,
        $expected_name,
        $expected_return,
        $paramCount,
        $description
    ) {
        ob_start();
        $tag = new MethodTag('method', $signature);
        $stdout = ob_get_clean();

        $this->assertSame(
            $valid,
            empty($stdout),
            'No error should have been output if the signature is valid'
        );

        if (!$valid) {
            return;
        }

        $this->assertEquals($expected_name, $tag->getMethodName());
        $this->assertEquals($expected_return, $tag->getType());
        $this->assertEquals($description, $tag->getDescription());
        $this->assertCount($paramCount, $tag->getArguments());
    }

    public function getTestSignatures()
    {
        return array(
            array(
                'foo',
                false, 'foo', '', 0, ''
            ),
            array(
                'foo()',
                true, 'foo', 'void', 0, ''
            ),
            array(
                'foo() description',
                true, 'foo', 'void', 0, 'description'
            ),
            array(
                'int foo()',
                true, 'foo', 'int', 0, ''
            ),
            array(
                'int foo() description',
                true, 'foo', 'int', 0, 'description'
            ),
            array(
                'int foo($a, $b)',
                true, 'foo', 'int', 2, ''
            ),
            array(
                'int foo() foo(int $a, int $b)',
                true, 'foo', 'int', 2, ''
            ),
            array(
                'int foo(int $a, int $b)',
                true, 'foo', 'int', 2, ''
            ),
            array(
                'null|int foo(int $a, int $b)',
                true, 'foo', 'null|int', 2, ''
            ),
            array(
                'int foo(null|int $a, int $b)',
                true, 'foo', 'int', 2, ''
            ),
            array(
                '\Exception foo() foo(Exception $a, Exception $b)',
                true, 'foo', '\Exception', 2, ''
            ),
            array(
                'int foo() foo(Exception $a, Exception $b) description',
                true, 'foo', 'int', 2, 'description'
            ),
            array(
                'int foo() foo(\Exception $a, \Exception $b) description',
                true, 'foo', 'int', 2, 'description'
            ),
        );
    }
}
