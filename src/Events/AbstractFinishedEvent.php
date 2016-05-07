<?php
/**
 * @author jsacha
 *
 * @since 28/04/16 21:19
 */

namespace Trivago\Rumi\Events;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractFinishedEvent extends Event
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';

    /**
     * @var
     */
    private $status;

    /**
     * RunFinishedEvent constructor.
     *
     * @param $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
}
