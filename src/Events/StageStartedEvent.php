<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 20:01
 */

namespace jakubsacha\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;

class StageStartedEvent extends Event
{
    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $jobs;

    /**
     * StageStartedEvent constructor.
     */
    public function __construct($name, $jobs)
    {
        $this->name = $name;
        $this->jobs = $jobs;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}
