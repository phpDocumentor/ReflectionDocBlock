<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Tag;
use Throwable;

/**
 * This class represents an exception during the tag creation
 *
 * Since the internals of the library are relaying on the correct syntax of a docblock
 * we cannot simply throw exceptions at all time because the exceptions will break the creation of a
 * docklock. Just silently ignore the exceptions is not an option because the user as an issue to fix.
 *
 * This tag holds that error information until a using application is able to display it. The object wil just behave
 * like any normal tag. So the normal application flow will not break.
 */
final class InvalidTag implements Tag
{
    /** @var string */
    private $name;

    /** @var string */
    private $body;

    /** @var Throwable|null */
    private $throwable;

    private function __construct(string $name, string $body)
    {
        $this->name = $name;
        $this->body = $body;
    }

    public function getException() : ?Throwable
    {
        return $this->throwable;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return self
     *
     * @inheritDoc
     */
    public static function create(string $body, string $name = '')
    {
        return new self($name, $body);
    }

    public function withError(Throwable $exception) : self
    {
        $tag            = new self($this->name, $this->body);
        $tag->throwable = $exception;

        return $tag;
    }

    public function render(?Formatter $formatter = null) : string
    {
        if ($formatter === null) {
            $formatter = new Formatter\PassthroughFormatter();
        }

        return $formatter->format($this);
    }

    public function __toString() : string
    {
        return $this->body;
    }
}
