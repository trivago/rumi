<?php
/**
 * @author jsacha
 * @since 20/11/2016 13:11
 */

namespace Trivago\Rumi\Models;


class StagesCollection implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $stages;

    /**
     * @param array $stages
     */
    public function __construct(array $stages = [])
    {
        $this->stages = $stages;
    }

    /**
     *
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->stages);
    }

}
