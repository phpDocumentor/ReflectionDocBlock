<?php
/**
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 */

declare(strict_types=1);

namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\Types\Context;
use RangeException;
use RuntimeException;
use function count;
use function implode;
use function sprintf;

abstract class BaseParser
{
    private const SYMBOL_NONE = -1;

    /*
     * The following members will be filled with generated parsing data:
     */

    /** @var int Size of $tokenToSymbol map */
    protected $tokenToSymbolMapSize;
    /** @var int Size of $action table */
    protected $actionTableSize;
    /** @var int Size of $goto table */
    protected $gotoTableSize;

    /** @var int Symbol number signifying an invalid token */
    protected $invalidSymbol;
    /** @var int Symbol number of error recovery token */
    protected $errorSymbol;
    /** @var int Action number signifying default action */
    protected $defaultAction;
    /** @var int Rule number signifying that an unexpected token was encountered */
    protected $unexpectedTokenRule;
    /** @var int states */
    protected $YY2TBLSTATE;
    /** @var int Number of non-leaf states */
    protected $numNonLeafStates;

    /** @var int[] Map of lexer tokens to internal symbols */
    protected $tokenToSymbol;
    /** @var string[] Map of symbols to their names */
    protected $symbolToName;
    /** @var string[] Names of the production rules (only necessary for debugging) */
    protected $productions;

    /**
     * @var int[] Map of states to a displacement into the $action table. The corresponding action for this
     *             state/symbol pair is $action[$actionBase[$state] + $symbol]. If $actionBase[$state] is 0, the
     * action is defaulted, i.e. $actionDefault[$state] should be used instead.
     */
    protected $actionBase;
    /** @var int[] Table of actions. Indexed according to $actionBase comment. */
    protected $action;
    /**
     * @var int[] Table indexed analogously to $action. If $actionCheck[$actionBase[$state] + $symbol] != $symbol
     *             then the action is defaulted, i.e. $actionDefault[$state] should be used instead.
     */
    protected $actionCheck;
    /** @var int[] Map of states to their default action */
    protected $actionDefault;
    /** @var callable[] Semantic action callbacks */
    protected $reduceCallbacks;


    /** @var TypeLexer */
    private $lexer;

    /** @var FqsenResolver */
    protected $fqsenResolver;

    /** @var Context */
    protected $context;

    /** @var Type|Type[]|null Temporary value containing the result of last semantic action (reduction) */
    protected $semValue;

    /** @var Type[] Semantic value stack (contains values of tokens and semantic action results) */
    protected $semStack;

    /**
     * @return void
     */
    //phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
    abstract protected function initReduceCallbacks();

    public function __construct()
    {
        $this->lexer = new DocBlockLexer();
        $this->initReduceCallbacks();
    }

    public function parse(string $type)
    {
        $this->lexer->setInput($type);
        $this->lexer->moveNext();

        // Start off in the initial state and keep a stack of previous states
        $state = 0;
        $stateStack = [$state];
        $symbol = self::SYMBOL_NONE;

        // Semantic value stack (contains values of tokens and semantic action results)
        $this->semStack = [];
        $this->semValue = null;

        $stackPos = 0;

        while (true) {
            $this->traceNewState($state, $symbol);
            if ($this->actionBase[$state] === 0) {
                $rule = $this->actionDefault[$state];
            } else {
                if ($symbol === self::SYMBOL_NONE) {
                    $this->lexer->moveNext();
                    $tokenId    = $this->lexer->token['type'] ?? 0;
                    $tokenValue = $this->lexer->token['value'] ?? null;

                    // map the lexer token id to the internally used symbols
                    $symbol = $tokenId >= 0 && $tokenId < $this->tokenToSymbolMapSize
                        ? $this->tokenToSymbol[$tokenId]
                        : $this->invalidSymbol;

                    if ($symbol === $this->invalidSymbol) {
                        throw new RangeException(sprintf(
                            'The lexer returned an invalid token (id=%d, value=%s)',
                            $tokenId,
                            $tokenValue
                        ));
                    }

                    $this->traceRead($symbol, $tokenValue);
                }

                $idx = $this->actionBase[$state] + $symbol;
                if ((($idx >= 0 && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol)
                        || ($state < $this->YY2TBLSTATE
                            && ($idx = $this->actionBase[$state + $this->numNonLeafStates] + $symbol) >= 0
                            && $idx < $this->actionTableSize && $this->actionCheck[$idx] === $symbol))
                    && ($action = $this->action[$idx]) !== $this->defaultAction) {
                    if ($action > 0) {
                        /** shift */
                        $this->traceShift($symbol);

                        ++$stackPos;
                        $stateStack[$stackPos] = $state = $action;
                        $this->semStack[$stackPos] = $tokenValue;
                        $symbol = self::SYMBOL_NONE;

                        if ($action < $this->numNonLeafStates) {
                            continue;
                        }

                        /* $yyn >= numNonLeafStates means shift-and-reduce */
                        $rule = $action - $this->numNonLeafStates;
                    } else {
                        $rule = -$action;
                    }
                } else {
                    $rule = $this->actionDefault[$state];
                }
            }

            for (;;) {
                if ($rule === 0) {
                    /* accept */
                    $this->traceAccept();

                    return $this->semValue;
                }

                if ($rule === $this->unexpectedTokenRule) {
                    /* error */
                    $msg = $this->getErrorMessage($symbol, $state);

                    throw new RuntimeException($msg);
                }

                /* reduce */
                $this->traceReduce($rule);
                $this->reduceCallbacks[$rule]($stackPos);

                $stackPos    -= $this->ruleToLength[$rule];
                $nonTerminal = $this->ruleToNonTerminal[$rule];
                $idx         = $this->gotoBase[$nonTerminal] + $stateStack[$stackPos];
                if ($idx >= 0 && $idx < $this->gotoTableSize && $this->gotoCheck[$idx] === $nonTerminal) {
                    $state = $this->goto[$idx];
                } else {
                    $state = $this->gotoDefault[$nonTerminal];
                }

                ++$stackPos;
                $stateStack[$stackPos]     = $state;
                $this->semStack[$stackPos] = $this->semValue;

                if ($state < $this->numNonLeafStates) {
                    break;
                }

                /* >= numNonLeafStates means shift-and-reduce */
                $rule = $state - $this->numNonLeafStates;
            }
        }

        throw new RuntimeException('Reached end of parser loop');
    }

    /**
     * Format error message including expected tokens.
     *
     * @param int $symbol Unexpected symbol
     * @param int $state  State at time of error
     *
     * @return string Formatted error message
     */
    protected function getErrorMessage(int $symbol, int $state) : string
    {
        $expectedString = '';
        if ($expected = $this->getExpectedTokens($state)) {
            $expectedString = ', expecting ' . implode(' or ', $expected);
        }

        return 'Docblock syntax error, unexpected ' . $this->symbolToName[$symbol] . $expectedString;
    }

    /**
     * Get limited number of expected tokens in given state.
     *
     * @param int $state State
     *
     * @return string[] Expected tokens. If too many, an empty array is returned.
     */
    protected function getExpectedTokens(int $state) : array
    {
        $expected = [];

        $base = $this->actionBase[$state];
        foreach ($this->symbolToName as $symbol => $name) {
            $idx = $base + $symbol;
            if ($idx < 0 || $idx >= $this->actionTableSize || ($this->actionCheck[$idx] !== $symbol
                    && $state >= $this->YY2TBLSTATE)
                || (isset($this->actionBase[$state + $this->numNonLeafStates]) &&
                    $idx = $this->actionBase[$state + $this->numNonLeafStates] + $symbol) < 0
                || $idx >= $this->actionTableSize ||
                (isset($this->actionCheck[$idx]) && $this->actionCheck[$idx] !== $symbol)
            ) {
                continue;
            }

            if (
                (isset($this->action[$idx]) &&
                    (
                        $this->action[$idx] === $this->unexpectedTokenRule
                        || $this->action[$idx] === $this->defaultAction
                    )
                )
                || $symbol === $this->errorSymbol
            ) {
                continue;
            }

            if (count($expected) === 4) {
                /* Too many expected tokens */
                return [];
            }

            $expected[] = $name;
        }

        return $expected;
    }

    protected function traceNewState($state, $symbol) : void
    {
        echo '% State ' . $state
            . ', Lookahead ' . ($symbol === self::SYMBOL_NONE ? '--none--' : $this->symbolToName[$symbol]) . "\n";
    }

    protected function traceRead($symbol, $value) : void
    {
        echo '% Reading ' . $this->symbolToName[$symbol] . " with value " . $value . "\n";
    }

    protected function traceShift($symbol) : void
    {
        echo '% Shift ' . $this->symbolToName[$symbol] . "\n";
    }

    protected function traceAccept() : void
    {
        echo "% Accepted.\n";
    }

    protected function traceReduce($n) : void
    {
        echo '% Reduce by (' . $n . ') ' . $this->productions[$n] . "\n";
    }

    protected function tracePop($state) : void
    {
        echo '% Recovering, uncovered state ' . $state . "\n";
    }

    protected function traceDiscard($symbol) : void
    {
        echo '% Discard ' . $this->symbolToName[$symbol] . "\n";
    }
}
