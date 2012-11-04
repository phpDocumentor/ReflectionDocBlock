<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Reflection class for a {@method} in a Docblock.
 *
 * @author  Mike van Riel <mike.vanriel@naenius.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    http://phpdoc.org
 */
class MethodTag extends ParamTag
{

    /** @var string */
    protected $method_name = '';

    /** @var string */
    protected $arguments = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @param string $type    Tag identifier for this tag (should be 'return')
     * @param string $content Contents for this tag.
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $this->content = $content;

        $matches = array();
        // 1. none or more whitespace
        // 2. optionally a word with underscores followed by whitespace : as
        //    type for the return value
        // 3. then optionally a word with underscores followed by () and
        //    whitespace : as method name as used by phpDocumentor
        // 4. then a word with underscores, followed by ( and any character
        //    until a ) and whitespace : as method name with signature
        // 5. any remaining text : as description
        if (preg_match(
            '/^[\s]*(?:([\w\|_\\\\]+)[\s]+)?(?:[\w_]+\(\)[\s]+)?([\w\|_\\\\]+)'
            .'\(([^\)]*)\)[\s]*(.*)/u',
            $content,
            $matches
        )) {
            list(
                ,
                $this->type,
                $this->method_name,
                $this->arguments,
                $this->description
            ) = $matches;
            if (!$this->type) {
                $this->type = 'void';
            }
        } else {
            echo date('c') . ' ERR (3): @method contained invalid contents: '
                . $this->content . PHP_EOL;
        }
    }

    /**
     * Sets the name of this method.
     *
     * @param string $method_name The name of the method.
     *
     * @return void
     */
    public function setMethodName($method_name)
    {
        $this->method_name = $method_name;
    }

    /**
     * Retrieves the method name.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->method_name;
    }

    /**
     * Sets the arguments for this method.
     *
     * @param string $arguments A comma-separated arguments line.
     *
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Returns an array containing each argument as array of type and name.
     *
     * Please note that the argument sub-array may only contain 1 element if no
     * type was specified.
     *
     * @return string[]
     */
    public function getArguments()
    {
        if (empty($this->arguments)) {
            return array();
        }

        $arguments = explode(',', $this->arguments);
        foreach ($arguments as $key => $value) {
            $arguments[$key] = explode(' ', trim($value));
        }

        return $arguments;
    }

}
