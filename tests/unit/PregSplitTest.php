<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\Exception\PcreException;
use PHPUnit\Framework\TestCase;

use function set_error_handler;

use const E_WARNING;

final class PregSplitTest extends TestCase
{
    /** @var callable|null */
    private $errorHandler = null;

    protected function tearDown(): void
    {
        if ($this->errorHandler === null) {
            return;
        }

        set_error_handler($this->errorHandler, E_WARNING);
    }

    /**
     * @covers \phpDocumentor\Reflection\Utils::pregSplit
     */
    public function testSimplePregSplit(): void
    {
        $result = Utils::pregSplit('/\s/', 'word split');

        $this->assertSame(['word', 'split'], $result);
    }

    /**
     * @covers \phpDocumentor\Reflection\Utils::pregSplit
     */
    public function testPregSplitThrowsOnError(): void
    {
        //We need to disable the error handler for phpunit... because we expect some errors here
        $this->errorHandler = set_error_handler(static function (): void {
        }, E_WARNING);

        $this->expectException(PcreException::class);
        Utils::pregSplit('~InvalidRegular)Expression~', 'some word');
    }
}
