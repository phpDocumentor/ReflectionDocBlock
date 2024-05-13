<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator as PhpStanTokenIterator;
use ReflectionObject;
use ReflectionProperty;

if (InstalledVersions::satisfies(
        new VersionParser(),
        'phpstan/phpdoc-parser',
        '<=1.29.0'
    )
) {
    final class TokenIterator extends PhpStanTokenIterator
    {
        private ReflectionProperty $indexProperty;
        private ReflectionProperty $tokensProperty;

        /** {@inheritDoc} */
        public function __construct(array $tokens, int $index = 0)
        {
            parent::__construct($tokens, $index);

            $r = new ReflectionObject($this);
            $this->indexProperty = $r->getParentClass()->getProperty('index');
            $this->indexProperty->setAccessible(true);
            $this->tokensProperty = $r->getParentClass()->getProperty('tokens');
            $this->tokensProperty->setAccessible(true);
        }

        public function getSkippedHorizontalWhiteSpaceIfAny(): string
        {
            $index = $this->indexProperty->getValue($this);
            $tokens = $this->tokensProperty->getValue($this);

            if (
                $index > 0 && (
                    $tokens[$index - 1][Lexer::TYPE_OFFSET] === Lexer::TOKEN_HORIZONTAL_WS ||
                    $tokens[$index - 1][Lexer::TYPE_OFFSET] === Lexer::TOKEN_PHPDOC_EOL)
            ) {
                return $tokens[$index - 1][Lexer::VALUE_OFFSET];
            }

            return '';
        }
    }
} else {
    class_alias(PhpStanTokenIterator::class, '\phpDocumentor\Reflection\DocBlock\Tags\Factory\TokenIterator');
}
