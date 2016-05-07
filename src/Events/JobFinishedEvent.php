<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 20:15
 */

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;

class JobFinishedEvent extends Event
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const STATUS_ABORTED = 'ABORTED';

    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $output;

    /**
     * JobFinishedEvent constructor.
     *
     * @param $status
     * @param $name
     * @param $output
     */
    public function __construct($status, $name, $output)
    {
        $this->status = $status;
        $this->name = $name;
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
