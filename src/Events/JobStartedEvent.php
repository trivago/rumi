<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 20:14
 */

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;

class JobStartedEvent extends Event
{
    /**
     * @var
     */
    private $name;

    /**
     * JobStartedEvent constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
