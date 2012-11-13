<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a @version tag in a Docblock.
 *
 * @author  Vasil Rangelov <boen.robot@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class VersionTag extends Tag
{
    /** @var string The version vector. */
    protected $version = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'version').
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;
        $this->description = trim($content);

        if (preg_match(
            '/^
                # The version vector
                ((?:
                    # Normal release vectors.
                    \d\S*
                    |
                    # VCS version vectors. Per PHPCS, they are expected to
                    # follow the form of the VCS name, followed by ":", followed
                    # by the version vector itself.
                    # By convention, popular VCSes like CVS, SVN and GIT use "$"
                    # around the actual version vector.
                    [^\s\:]+\:\s*\$[^\$]+\$
                ))
                \s*
                # The description
                (.+)?
            $/sux',
            $this->description,
            $matches
        )) {
            $this->version = $matches[1];
            $this->description = isset($matches[2]) ? $matches[2] : '';
        }
    }

    /**
     * Returns the version section of the tag.
     *
     * @return string The version section of the tag.
     */
    public function getVersion()
    {
        return $this->version;
    }
}
