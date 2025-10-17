<?php

namespace App\Services\Azure\NotificationHub;

class NotificationTagBuilder
{
    /**
     * The result tag.
     */
    protected string $result;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(string $tag)
    {
        $this->result = $tag;
    }

    /**
     * Add new tag with AND operator.
     */
    public function tag(string|callable $tag): NotificationTagBuilder
    {
        if (! str_ends_with($this->result, '(')) {
            $this->result .= ' && ';
        }

        if (is_callable($tag)) {
            $this->result .= '(';
            $tag($this);
            $this->result .= ')';
        } else {
            $this->result .= $tag;
        }

        return $this;
    }

    /**
     * Add new tag with OR operator.
     */
    public function orTag(string|callable $tag): NotificationTagBuilder
    {
        if (! str_ends_with($this->result, '(')) {
            $this->result .= ' || ';
        }

        if (is_callable($tag)) {
            $this->result .= '(';
            $tag($this);
            $this->result .= ')';
        } else {
            $this->result .= $tag;
        }

        return $this;
    }

    /**
     * Add new tag with NOT operator.
     */
    public function notTag(string|callable $tag): NotificationTagBuilder
    {
        if (! str_ends_with($this->result, '(')) {
            $this->result .= ' && ';
        }

        if (is_callable($tag)) {
            $this->result .= '!(';
            $tag($this);
            $this->result .= ')';
        } else {
            $this->result .= '!'.$tag;
        }

        return $this;
    }

    /**
     * Get the result tag.
     */
    public function get(): string
    {
        return $this->result;
    }
}
