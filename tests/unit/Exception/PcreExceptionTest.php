<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\Exception;

use PHPUnit\Framework\TestCase;

use const PREG_BACKTRACK_LIMIT_ERROR;
use const PREG_BAD_UTF8_ERROR;
use const PREG_BAD_UTF8_OFFSET_ERROR;
use const PREG_INTERNAL_ERROR;
use const PREG_JIT_STACKLIMIT_ERROR;
use const PREG_NO_ERROR;
use const PREG_RECURSION_LIMIT_ERROR;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\Exception\PcreException
 */
final class PcreExceptionTest extends TestCase
{
    /**
     * @covers ::createFromPhpError
     * @dataProvider errorCodeProvider
     */
    public function testErrorConversion(int $errorCode, string $message): void
    {
        $this->assertSame($message, PcreException::createFromPhpError($errorCode)->getMessage());
    }

    /**
     * @return array<int, (string|int)[]>
     */
    public function errorCodeProvider(): array
    {
        return [
            [
                PREG_BACKTRACK_LIMIT_ERROR,
                'Backtrack limit error',
            ],
            [
                PREG_RECURSION_LIMIT_ERROR,
                'Recursion limit error',
            ],
            [
                PREG_BAD_UTF8_ERROR,
                'Bad UTF8 error',
            ],
            [
                PREG_BAD_UTF8_OFFSET_ERROR,
                'Bad UTF8 offset error',
            ],
            [
                PREG_JIT_STACKLIMIT_ERROR,
                'Jit stacklimit error',
            ],
            [
                PREG_NO_ERROR,
                'Unknown Pcre error',
            ],
            [
                PREG_INTERNAL_ERROR,
                'Unknown Pcre error',
            ],
        ];
    }
}
