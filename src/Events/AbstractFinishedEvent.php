<?php
/**
 * @author jsacha
 * @since 28/04/16 21:19
 */

namespace jakubsacha\Rumi\Events;


use Symfony\Component\EventDispatcher\Event;

abstract class AbstractFinishedEvent extends Event
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    
    /**
     * @var
     */
    private $status;

    /**
     * RunFinishedEvent constructor.
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