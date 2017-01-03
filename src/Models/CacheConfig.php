<?php

namespace Trivago\Rumi\Models;


class CacheConfig implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $directories;

    /**
     * CacheConfig constructor.
     * @param array $directories
     */
    public function __construct(array $directories = [])
    {
        $this->directories = $directories;
    }

    /**
     *
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->directories);
    }

    public function count()
    {
        return count($this->directories);
    }
}
