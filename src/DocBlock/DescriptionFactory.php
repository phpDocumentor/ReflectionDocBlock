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

use phpDocumentor\Reflection\Types\Context;

class DescriptionFactory
{
    /** @var TagFactory */
    private $tagFactory;

    /**
     * Initializes this factory with the means to construct (inline) tags.
     *
     * @param TagFactory $tagFactory
     */
    public function __construct(TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }

    /**
     * Returns the parsed text of this description.
     *
     * @param string $contents
     * @param Context $context
     *
     * @return Description
     */
    public function create($contents, Context $context = null)
    {
        list($text, $tags) = $this->parse($this->lex($contents), $context);

        return new Description($text, $tags);
    }

    /**
     * @param $contents
     * @return array
     */
    private function lex($contents)
    {
        // performance optimalization; if there is no inline tag, don't bother splitting it up.
        if (strpos($contents, '{@') === false) {
            return [$contents];
        }

        return preg_split(
            '/\{
                # "{@}" is not a valid inline tag. This ensures that we do not treat it as one, but treat it literally.
                (?!@\})
                # We want to capture the whole tag line, but without the inline tag delimiters.
                (\@
                    # Match everything up to the next delimiter.
                    [^{}]*
                    # Nested inline tag content should not be captured, or it will appear in the result separately.
                    (?:
                        # Match nested inline tags.
                        (?:
                            # Because we did not catch the tag delimiters earlier, we must be explicit with them here.
                            # Notice that this also matches "{}", as a way to later introduce it as an escape sequence.
                            \{(?1)?\}
                            |
                            # Make sure we match hanging "{".
                            \{
                        )
                        # Match content after the nested inline tag.
                        [^{}]*
                    )* # If there are more inline tags, match them as well. We use "*" since there may not be any
                       # nested inline tags.
                )
            \}/Sux',
            $contents,
            null,
            PREG_SPLIT_DELIM_CAPTURE
        );
    }

    /**
     * Parses the stream of tokens in to a new set of tokens containing Tags.
     *
     * @param string[] $tokens
     * @param Context $context
     *
     * @return string[]|Tag[]
     */
    private function parse($tokens, Context $context)
    {
        $count = count($tokens);
        $tagCount = 0;
        $tags = [];
        for ($i = 1; $i < $count; $i += 2) {
            $tags[] = $this->tagFactory->create($tokens[$i], $context);
            $tokens[$i] = '%' . ++$tagCount . '$s';
        }

        //In order to allow "literal" inline tags, the otherwise invalid
        //sequence "{@}" is changed to "@", and "{}" is changed to "}".
        //See unit tests for examples.
        for ($i = 0; $i < $count; $i += 2) {
            $tokens[$i] = str_replace(['{@}', '{}'], ['@', '}'], $tokens[$i]);
        }

        return [implode('', $tokens), $tags];
    }

}
